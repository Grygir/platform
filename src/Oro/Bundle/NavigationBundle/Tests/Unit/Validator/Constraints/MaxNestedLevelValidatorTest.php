<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Validator\Constraints;

use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Oro\Bundle\NavigationBundle\Provider\BuilderChainProvider;
use Oro\Bundle\NavigationBundle\Tests\Unit\Entity\Stub\MenuUpdateStub;
use Oro\Bundle\NavigationBundle\Tests\Unit\MenuItemTestTrait;
use Oro\Bundle\NavigationBundle\Validator\Constraints\MaxNestedLevel;
use Oro\Bundle\NavigationBundle\Validator\Constraints\MaxNestedLevelValidator;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\UserBundle\Entity\User;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

class MaxNestedLevelValidatorTest extends \PHPUnit_Framework_TestCase
{
    use MenuItemTestTrait;

    /** @var MaxNestedLevelValidator */
    protected $validator;

    /** @var BuilderChainProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $builderChainProvider;

    /** @var ExecutionContextInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->context = $this->getMock(ExecutionContextInterface::class);

        $this->builderChainProvider = $this->getMock(BuilderChainProvider::class, [], [], '', false);

        /** @var LocalizationHelper|\PHPUnit_Framework_MockObject_MockObject $localizationHelper */
        $localizationHelper = $this->getMock(LocalizationHelper::class, [], [], '', false);

        $this->validator = new MaxNestedLevelValidator($this->builderChainProvider, $localizationHelper);
        $this->validator->initialize($this->context);
    }

    public function testValidateNotValid()
    {
        $constraint = new MaxNestedLevel();

        $menu = $this->getMenu();
        $menu->setExtra('max_nesting_level', 2);

        $scope = $this->getMock(Scope::class, ['getOrganization', 'getUser']);
        $user = new User();
        $organization = new Organization();
        $scope->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $scope->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $update = new MenuUpdateStub();
        $update->setScope($scope);
        $update->setMenu('menu');
        $update->setKey('item-1-1-1');
        $update->setParentKey('item-1-1');
        $update->setUri('#');

        $this->builderChainProvider
            ->expects($this->once())
            ->method('get')
            ->with(
                'menu',
                [
                    'ignoreCache' => true,
                    'scopeContext' => ['user' => $user, 'organization' => $organization]
                ]
            )
            ->will($this->returnValue($menu));

        $this->context
            ->expects($this->once())
            ->method('addViolation')
            ->with("Item \"item-1-1-1\" can't be saved. Max nesting level is reached.");


        $this->validator->validate($update, $constraint);

        $item = $menu->getChild('item-1')
            ->getChild('item-1-1')
            ->getChild('item-1-1-1');

        $this->assertNotNull($item);
    }

    public function testValidateNotValidNew()
    {
        $constraint = new MaxNestedLevel();

        $menu = $this->getMenu();
        $menu->setExtra('max_nesting_level', 3);

        $scope = $this->getMock(Scope::class, ['getOrganization', 'getUser']);
        $user = new User();
        $organization = new Organization();
        $scope->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $scope->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);
        $update = new MenuUpdateStub();
        $update->setScope($scope);
        $update->setMenu('menu');
        $update->setKey('item-1-1-1-1');
        $update->setParentKey('item-1-1-1');
        $update->setUri('#');
        $update->setCustom(true);

        $this->builderChainProvider
            ->expects($this->once())
            ->method('get')
            ->with(
                'menu',
                [
                    'ignoreCache' => true,
                    'scopeContext' => ['user' => $user, 'organization' => $organization]
                ]
            )
            ->will($this->returnValue($menu));

        $this->context
            ->expects($this->once())
            ->method('addViolation')
            ->with("Item \"item-1-1-1-1\" can't be saved. Max nesting level is reached.");

        $this->validator->validate($update, $constraint);

        $item = $menu->getChild('item-1')
            ->getChild('item-1-1')
            ->getChild('item-1-1-1')
            ->getChild('item-1-1-1-1');

        $this->assertNull($item);
    }

    public function testValidateIsValid()
    {
        $constraint = new MaxNestedLevel();

        $menu = $this->getMenu();
        $menu->setExtra('max_nesting_level', 4);

        $update = new MenuUpdateStub();

        $scope = $this->getMock(Scope::class, ['getOrganization', 'getUser']);
        $user = new User();
        $organization = new Organization();
        $scope->expects($this->once())
            ->method('getUser')
            ->willReturn($user);
        $scope->expects($this->once())
            ->method('getOrganization')
            ->willReturn($organization);

        $update->setScope($scope);
        $update->setMenu('menu');
        $update->setKey('item-1-1-1-1');
        $update->setParentKey('item-1-1-1');
        $update->setUri('#');

        $this->builderChainProvider
            ->expects($this->once())
            ->method('get')
            ->with(
                'menu',
                [
                    'ignoreCache' => true,
                    'scopeContext' => ['user' => $user, 'organization' => $organization]
                ]
            )
            ->will($this->returnValue($menu));

        $this->validator->validate($update, $constraint);
    }
}
