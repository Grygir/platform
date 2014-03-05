<?php

namespace Oro\Bundle\EntityExtendBundle\Extend\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Table;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendConfigDumper;

class ExtendTable extends Table
{
    /**
     * @var ExtendOptionManager
     */
    protected $extendOptionManager;

    /**
     * @param ExtendOptionManager $extendOptionManager
     * @param Table               $baseTable
     */
    public function __construct(ExtendOptionManager $extendOptionManager, Table $baseTable)
    {
        $this->extendOptionManager = $extendOptionManager;

        parent::__construct(
            $baseTable->getName(),
            $baseTable->getColumns(),
            $baseTable->getIndexes(),
            $baseTable->getForeignKeys(),
            false,
            $baseTable->getOptions()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addOption($name, $value)
    {
        if ($name === ExtendColumn::ORO_OPTIONS_NAME) {
            $this->extendOptionManager->setTableOptions($this->getName(), $value);

            return $this;
        }

        return parent::addOption($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn($columnName, $typeName, array $options = [])
    {
        $oroOptions = null;
        if (isset($options[ExtendColumn::ORO_OPTIONS_NAME])) {
            $oroOptions = $options[ExtendColumn::ORO_OPTIONS_NAME];
            unset($options[ExtendColumn::ORO_OPTIONS_NAME]);
        }

        if (null !== $oroOptions && isset($oroOptions['extend'])) {
            $columnName         = ExtendConfigDumper::FIELD_PREFIX . $columnName;
            $options['notnull'] = false;
        }

        parent::addColumn($columnName, $typeName, $options);

        $column = $this->getColumn($columnName);
        if (null !== $oroOptions) {
            $column->setOptions([ExtendColumn::ORO_OPTIONS_NAME => $oroOptions]);
        }

        return $column;
    }

    /**
     * {@inheritdoc}
     */
    // @codingStandardsIgnoreStart
    protected function _addColumn(Column $column)
    {
        if (!($column instanceof ExtendColumn)) {
            $column = new ExtendColumn($this->extendOptionManager, $this->getName(), $column);
        }
        parent::_addColumn($column);
    }
    // @codingStandardsIgnoreEnd
}
