<?php

/**
 * @var $version
 * @var $description
 * @var $extendUse
 * @var $extendClass
 * @var $moduleVersion
 * @var $smartProcessExport
 * @var $smartProcess
 * @var $smartProcessFields
 * @var $smartProcessPermissions
 * @var $exportElementForm
 * @var $exportElementList
 * @var $author
 * @var $smartProcessCode
 * @var $smartprocessTransferStages
 * @var $smartprocessTransferFunnels
 * @var $oldSmartProcessEntityTypeId
 * @var $smartprocessBusinessProcesses
 * @formatter:off
 */

?><?php echo "<?php\n" ?>

namespace Incrootal\Migration;

<?php echo $extendUse ?>

class <?php echo $version ?> extends <?php echo $extendClass ?>

{
protected $author = "<?php echo $author ?>";

protected $description = "<?php echo $description ?>";

protected $moduleVersion = "<?php echo $moduleVersion ?>";

protected $smartProcessCode = "<?php echo $smartProcessCode ?>";
protected $oldSmartProcessEntityTypeId = "<?php echo $oldSmartProcessEntityTypeId ?>";

/**
* @throws Exceptions\HelperException
* @return bool|void
*/
public function up()
{
$helper = $this->getHelperManager();
<?php if (!empty($smartProcessExport)): ?>
    $helper->SmartProcess()->createSmartProcess(<?php echo var_export($smartProcess, 1) ?>);
<?php else:?>

<?php endif; ?>
<?php if (!empty($smartProcessPermissions)): ?>
    $helper->SmartProcess()->saveGroupPermissions($smartProcessId, <?php echo var_export($smartProcessPermissions, 1) ?>);
<?php endif?>
<?php if (!empty($smartProcessFields)): ?>
    $entityId = 'CRM_' . $helper->SmartProcess()->getSmartProcessesId($this->smartProcessCode);
    <?php foreach ($smartProcessFields as $field) { ?>

        $userTypeEntityData = (<?php echo var_export($field, 1) ?>);

        $userTypeEntityData['ENTITY_ID'] = $entityId;
        $helper->UserTypeEntity()->saveUserTypeEntity($userTypeEntityData);

    <?php } ?>
<?php endif?>
<?php if (!empty($smartprocessTransferFunnels)): ?>
    $entityTypeId = $helper->SmartProcess()->getSmartProcessesEntityTypeId($this->smartProcessCode);
    <?php foreach ($smartprocessTransferFunnels as $funnel) { ?>
        $array = (<?php echo var_export($funnel, 1) ?>);

        $array['ENTITY_TYPE_ID'] = $entityTypeId;

        $helper->SmartProcess()->addTunell($array);
    <?php } ?>
<?php endif?>
<?php if (!empty($smartprocessTransferStages)): ?>
    <?php foreach ($smartprocessTransferStages as $field) {

        if (isset($field['ENTITY_ID'])) {
            $field['ENTITY_ID'] = preg_replace('/' . preg_quote($oldSmartProcessEntityTypeId, '/') . '/', '#ID#', $field['ENTITY_ID'], 1);
        }
        if (isset($field['STATUS_ID'])) {
            $field['STATUS_ID'] = preg_replace('/' . preg_quote($oldSmartProcessEntityTypeId, '/') . '/', '#ID#', $field['STATUS_ID'], 1);
        }
        ?>
            $transferStage = <?php echo var_export($field, 1) ?>;
        $transferStage['ENTITY_ID'] = str_replace('#ID#', $this->oldSmartProcessEntityTypeId, $transferStage['ENTITY_ID']);
        $transferStage['STATUS_ID'] = str_replace('#ID#', $this->oldSmartProcessEntityTypeId, $transferStage['STATUS_ID']);
        $helper->SmartProcess()->addStatus($transferStage);
    <?php } ?>
<?php endif?>
<?php if (!empty($exportElementForm)): ?>
    $helper->UserOptions()->saveSmartProcessForm($smartProcessId, <?php echo var_export($exportElementForm, 1) ?>);
<?php endif?>
<?php if (!empty($exportElementList)): ?>
    $helper->UserOptions()->saveSmartProcessList($smartProcessId, <?php echo var_export($exportElementList, 1) ?>);
<?php endif?>


<?php if (!empty($smartprocessBusinessProcesses)): ?>
    $workflowTemplate = new WorkflowTemplate();
    <?php foreach ($smartprocessBusinessProcesses as $entity): ?>
        $workflowTemplate->importWorkflowTemplate(<?= var_export($entity, true) ?>);
    <?php endforeach; ?>
<?php endif?>

}
}
