<?php

/**
 * @var $version
 * @var $description
 * @var $extendUse
 * @var $extendClass
 * @var $moduleVersion
 * @var $entities
 * @formatter:off
 */

?>
<?= "<?php\n" ?>


namespace Incrootal\Migration;

<?= $extendUse ?>
use Ibs\Migration\WorkflowTemplate\WorkflowTemplate;

class <?= $version ?> extends <?= $extendClass ?>

{
protected $description = "<?= $description ?>";

protected $moduleVersion = "<?= $moduleVersion ?>";

/**
* @throws Exceptions\HelperException
* @return bool|void
*/
public function up()
{
$workflowTemplate = new WorkflowTemplate();
<?php foreach ($entities as $entity): ?>
    $workflowTemplate->importWorkflowTemplate(<?= var_export($entity, true) ?>);
<?php endforeach; ?>
}
}