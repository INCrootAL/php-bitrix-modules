<?php

namespace Incrootal\Migration\Builders;

use Bitrix\Bizproc\Copy\Implement\WorkflowTemplate;
use Incrootal\Migration\Exceptions\HelperException;
use Incrootal\Migration\Exceptions\MigrationException;
use Incrootal\Migration\Exceptions\RebuildException;
use Incrootal\Migration\Locale;
use Incrootal\Migration\Module;
use Incrootal\Migration\VersionBuilder;

class SmartProcessBuilder extends VersionBuilder
{
    protected function isBuilderEnabled()
    {
        $isEnabled = $this->getHelperManager()->SmartProcess()->isEnabled();
        return $isEnabled;
    }

    protected function initialize()
    {
        $this->setTitle(Locale::getMessage('BUILDER_SmartProcessExport1'));
        $this->setDescription(Locale::getMessage('BUILDER_SmartProcessExport2'));
        $this->setGroup('SmartProcess');

        $this->addVersionFields();
    }

    /**
     * @throws HelperException
     * @throws RebuildException
     * @throws MigrationException
     */
    protected function execute()
    {
        $helper = $this->getHelperManager();

        $smartProcessId = $this->addFieldAndReturn(
            'smartprocess_id',
            [
                'title' => Locale::getMessage('BUILDER_SmartProcessExport_SmartProcessId'),
                'placeholder' => '',
                'width' => 250,
                'select' => $this->getHelperManager()->SmartProcess()->getSmartProcessesStructure(),
            ]
        );

        $dataSP = $helper->SmartProcess()->getSmartProcesses($smartProcessId);
        $versionParts = ['SP_' . $dataSP['CODE']];

        if (empty($smartProcessId)) {
            $this->rebuildField('smartprocess_id');
        }

        $what = $this->addFieldAndReturn(
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
                    [
                        'title' => Locale::getMessage('BUILDER_SmartProcessExport_WhatSmartProcessFields'),
                        'value' => 'smartprocessFields',
                    ],
                    [
                        'title' => Locale::getMessage('BUILDER_SmartProcessExport_WhatSmartProcessTransferStages'),
                        'value' => 'smartprocessTransferStages',
                    ],
                    [
                        'title' => Locale::getMessage('BUILDER_SmartProcessExport_WhatSmartProcessTransferFunnels'),
                        'value' => 'smartprocessTransferFunnels',
                    ],
/*                    [
                        'title' => Locale::getMessage('BUILDER_SmartProcessExport_WhatSmartProcessBusinessProcesses'),
                        'value' => 'smartprocessBusinessProcesses',
                    ],*/
                    //TODO Доделать
                    /*[
                        'title' => Locale::getMessage('BUILDER_SmartProcessExport_WhatSmartProcessUserOptions'),
                        'value' => 'smartprocessUserOptions',
                    ],
                    [
                        'title' => Locale::getMessage('BUILDER_SmartProcessExport_WhatSmartProcessPermissions'),
                        'value' => 'smartprocessPermissions',
                    ],*/
                ],
            ]
        );

        $smartProcessExport = false;
        $smartProcessFields = [];
        $smartProcessPermissions = [];
        $exportElementForm = [];
        $exportElementList = [];
        $smartProcessTransferStatus = [];
        $transferFunnels = [];
        $entityTypeId = $helper->SmartProcess()->getSmartProcessesEntityTypeId($dataSP['CODE']);
        if (in_array('smartprocess', $what)) {
            $smartProcessFields = $helper->SmartProcess()->exportFields('CRM_' . $smartProcessId);
            $smartProcessTransferStatus = $helper->SmartProcess()->getSmartProcessesStatus($dataSP);
            $transferFunnels = $helper->SmartProcess()->getSmartProcessesTransferFunnels($dataSP);
            $smartProcessExport = true;


            $this->finalizeExport($dataSP, $smartProcessFields, $smartProcessPermissions, $exportElementForm, $exportElementList, $smartProcessTransferStatus, $transferFunnels, $smartProcessExport, $versionParts,$entityTypeId,$smartprocessBusinessProcesses);
            return;
        }

        if (in_array('smartprocessFields', $what)) {
            $smartProcessFields = $helper->SmartProcess()->exportFields('CRM_' . $smartProcessId);
        }
        if (in_array('smartprocessBusinessProcesses', $what)) {
            $smartprocessBusinessProcesses = [];

            /*$workflowTemplates = $helper->SmartProcess()->getWorkflowTemplates($entityTypeId);*/
            $smartprocessBusinessProcesses[] = $helper->SmartProcess()->getWorkflowTemplates($entityTypeId);
            // Запись в $entities экспортированных шаблонов БП

        }

        if (in_array('smartprocessTransferStages', $what)) {
            $smartProcessTransferStatus = $helper->SmartProcess()->getSmartProcessesStatus($dataSP);
        }

        if (in_array('smartprocessTransferFunnels', $what)) {
            $transferFunnels = $helper->SmartProcess()->getSmartProcessesTransferFunnels($dataSP);
        }


        if (!$smartProcessExport) {
            if (in_array('smartprocessFields', $what)) {
                $versionParts[] = 'FIELDS';
            }
            if (in_array('smartprocessTransferStages', $what)) {
                $versionParts[] = 'STATUS';
            }
            if (in_array('smartprocessTransferFunnels', $what)) {
                $versionParts[] = 'FUNNELS';
            }
        }
        $versionPrefix = $this->purifyPrefix($this->getFieldValue('prefix'));
        if ($versionPrefix && $versionPrefix !== 'Version') {
            $versionParts[] = $versionPrefix;
        }
        //TODO Доделать
        /* if (in_array('smartprocessUserOptions', $what)) {
             $exportElementForm = $helper->SmartProcess()->exportFields($smartProcessId);
             $exportElementList = $helper->UserOptions()->exportSmartProcessList($smartProcessId);
         }
         if (in_array('smartprocessPermissions', $what)) {
             $smartProcessPermissions = $helper->SmartProcess()->exportGroupPermissions($smartProcessId);
         }*/
        $this->finalizeExport($dataSP, $smartProcessFields, $smartProcessPermissions, $exportElementForm, $exportElementList, $smartProcessTransferStatus, $transferFunnels, $smartProcessExport, $versionParts,$entityTypeId,$smartprocessBusinessProcesses);
    }

    private function finalizeExport($dataSP, $smartProcessFields, $smartProcessPermissions, $exportElementForm, $exportElementList, $smartProcessTransferStatus, $transferFunnels, $smartProcessExport, $versionParts,$entityTypeId,$smartprocessBusinessProcesses)
    {
        $timestamp = date('YmdHis');
        $versionParts[] = $timestamp;
        $version = implode('_', $versionParts);

        $this->createVersionFile(
            Module::getModuleDir() . '/templates/SmartProcessExport.php',
            [
                'smartProcessCode' => $dataSP['CODE'],
                'smartProcessExport' => $smartProcessExport,
                'smartProcess' => $dataSP,
                'smartProcessFields' => $smartProcessFields,
                'smartProcessPermissions' => $smartProcessPermissions,
                'exportElementForm' => $exportElementForm,
                'exportElementList' => $exportElementList,
                'smartprocessTransferStages' => $smartProcessTransferStatus,
                'smartprocessTransferFunnels' => $transferFunnels,
                'oldSmartProcessEntityTypeId' => $entityTypeId,
                'smartprocessBusinessProcesses' => $smartprocessBusinessProcesses,
                'version' => $version,
            ]
        );
    }
}
