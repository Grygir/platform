<?php

namespace Oro\Bundle\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use YsTools\BackUrlBundle\Annotation\BackUrl;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Datagrid\RoleDatagridManager;

/**
 * @Route("/role")
 * @Acl(
 *      id = "oro_role",
 *      name="Role controller",
 *      description = "Role manipulation"
 * )
 * @BackUrl("back", useSession=true)
 */
class RoleController extends Controller
{
    /**
     * Create role form
     *
     * @Route("/create", name="oro_user_role_create")
     * @Template("OroUserBundle:Role:edit.html.twig")
     */
    public function createAction()
    {
        return $this->editAction(new Role());
    }

    /**
     * Edit role form
     *
     * @Route("/edit/{id}", name="oro_user_role_edit", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     */
    public function editAction(Role $entity)
    {
        if ($this->get('oro_user.form.handler.role')->process($entity)) {
            $this->get('session')->getFlashBag()->add('success', 'Role successfully saved');

            BackUrl::triggerRedirect();

            return $this->redirect($this->generateUrl('oro_user_role_index'));
        }

        /** @var $gridManager RoleDatagridManager */
        $gridManager = $this->get('oro_user.role_user_datagrid_manager');

        $this->initializeQueryFactory($entity);
        $gridManager->getRouteGenerator()->setRouteParameters(array('id' => $entity->getId()));

        $datagrid = $gridManager->getDatagrid();

        return array(
            'datagrid' => $datagrid->createView(),
            'form'     => $this->get('oro_user.form.role')->createView(),
        );
    }

    /**
     * Get grid users data
     *
     * @Route(
     *  "/grid/{id}",
     *  name="oro_user_role_user_grid",
     *  requirements={"id"="\d+"},
     *  defaults={"id"=0, "_format"="json"}
     * )
     * @Template("OroGridBundle:Datagrid:list.json.php")
     */
    public function gridDataAction(Role $entity)
    {
        $this->initializeQueryFactory($entity);

        $datagrid = $this->get('oro_user.role_user_datagrid_manager')->getDatagrid();

        return array('datagrid' => $datagrid->createView());
    }

    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_user_role_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id = "oro_role_list",
     *      name="Role list",
     *      description = "List of roles",
     *      parent = "oro_role"
     * )
     */
    public function indexAction(Request $request)
    {
        $datagrid = $this->get('oro_user.role_datagrid_manager')->getDatagrid();
        $view     = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroUserBundle:Role:index.html.twig';

        return $this->render(
            $view,
            array('datagrid' => $datagrid->createView())
        );
    }

    /**
     * @param Role $entity
     */
    protected function initializeQueryFactory(Role $entity)
    {
        $this->get('oro_user.role_user_datagrid_manager.default_query_factory')
            ->setQueryBuilder(
                $this->get('oro_user.role_manager')->getUserQueryBuilder($entity)
            );
    }
}
