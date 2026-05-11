<?php

namespace Incrootal\Migration\Builders;

use Incrootal\Migration\Exceptions\HelperException;
use Incrootal\Migration\Exceptions\MigrationException;
use Incrootal\Migration\Exceptions\RebuildException;
use Incrootal\Migration\Helpers\WorkFlowTemplateHelper;
use Incrootal\Migration\Locale;
use Incrootal\Migration\Module;
use Incrootal\Migration\VersionBuilder;

class WorkFlowTemplateBuilder extends VersionBuilder
{
    protected function isBuilderEnabled()
    {
        $isEnabled = $this->getHelperManager()->WorkFlowTemplate()->isEnabled();

        return $isEnabled;
    }

    protected function initialize()
    {

        $this->setTitle(Locale::getMessage('BUILDER_WorkFlowTemplateExport1'));
        $this->setGroup('WorkFlowTemplate');

        $this->addVersionFields();

        $bp = $this->getHelperManager()->WorkFlowTemplate()->exportBP(58);


    }

    /**
     * @throws HelperException
     * @throws RebuildException
     * @throws MigrationException
     */
    protected function execute()
    {

        $helper = $this->getHelperManager();
       /* $workflowTemplates = $this->addFieldAndReturn(
            'WorkFlowTemplateBuilder',
            [
                'title' => Locale::getMessage('BUILDER_WorkFlowTemplateEntities'),
                'placeholder' => '',
                'multiple' => 1,
                'width' => 250,
                'items' =>  $this->getHelperManager()->WorkFlowTemplate()->getList(),
                'value' => [],
            ]
        );*/

       /* $what = $this->addFieldAndReturn(
            'what', [
                'title' => Locale::getMessage('BUILDER_SmartProcessExport_What'),
                'width' => 250,
                'multiple' => 1,
                'value' => [],
                'select' => [
                    [
                        'title' => Locale::getMessage('BUILDER_SmartProcessExport_WhatSmartProcess'),
                        'value' => 'smartprocess',
                    ],

                ]
            ]
        );*/

        $entities = [];

        $workflowTemplateEntity = new WorkFlowTemplateHelper();
        // Запись в $entities экспортированных шаблонов БП
        foreach ($workflowTemplates as $workflowTemplate) {
            $entities[] = $workflowTemplateEntity->exportWorkflowTemplateEntity($workflowTemplate);
        }

        // Создание файла миграции с описаниями шаблонов БП
        $this->createVersionFile(
            Module::getModuleDir() . '/templates/WorkflowTemplateEntities.php',
            [
                'entities' => $entities,
            ]
        );
    }

}