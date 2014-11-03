<p style="font-style: italic"><?= $folder->comment ?></p>
<?php
$buttons=array(
    'view'=>
        array(
            'visible'=>'true',
            'url'=>'Yii::app()->createUrl("/mff/formview/save", 
                    array(
                        "idregistry"=>$data->registry,
                        "idstorage"=>$data->storage,
                        "idform"=>$data->id,
                        "scenario"=>"view",
                        "layouts"=>"'.base64_encode("/cabinet/cabinet").'",
                        "addons"=>"'.base64_encode('array("cabinetid"=>'.$cabinet->id.')').
                        '")
                    )'          
            ),
    'update'=>
        array(
            'visible'=>'false',
            'url'=>'Yii::app()->createUrl("/mff/formview/save", 
                    array(
                        "idregistry"=>$data->registry,
                        "idstorage"=>$data->storage,
                        "idform"=>$data->id,
                        "scenario"=>"update",
                        "layouts"=>"'.base64_encode("/cabinet/cabinet").'",
                        "addons"=>"'.base64_encode('array("cabinetid"=>'.$cabinet->id.')').
                        '")
                    )'          
            ),
    'delete'=>
        array(
            'visible'=>'false',
            'url'=>'Yii::app()->createUrl("/mff/formview/delete", 
                    array(
                        "idstorage"=>$data->storage,
                        "idform"=>$data->id,
                        "layouts"=>"'.base64_encode("/cabinet/cabinet").'",
                        "addons"=>"'.base64_encode('array("cabinetid"=>'.$cabinet->id.')').
                        '")
                    )'          
            ),
    ); 
if ($folder->getItems("allow_edit")>0) {
    $buttons["update"]["visible"]="true";
}
if ($folder->getItems("allow_delete")>0) {
    $buttons["delete"]["visible"]="true";
}
$storageItems=$folder->getItems("allow_new");
if (count($storageItems)>0) {
    $items=array();
    foreach ($storageItems as $storageItem) {
        $storageItem=FFStorage::model()->findByPk($storageItem->id);
        foreach ($storageItem->registryItems as $registryItem) {
            $label = "Новый: ".$registryItem->getAttribute("description")." (".$storageItem->getAttribute("description").")";
            $url=$this->createUrl("/mff/formview/save", array("idregistry"=>$registryItem->id,"idstorage"=>$storageItem->id,"layouts"=>base64_encode("/cabinet/cabinet"),"addons"=>base64_encode('array("cabinetid"=>'.$cabinet->id.')')));
            $items=array_merge($items,array(array("label"=>$label,"url"=>$url)));                    
        }
    }
    $this->widget("zii.widgets.CMenu",array("items"=>$items,"htmlOptions"=>array("id"=>"menucreate")));
    $urldata=array();
    if (isset($idregistry)) $urldata=array_merge($urldata,array("idregistry"=>$idregistry,));
    if (isset($idstorage)) $urldata=array_merge($urldata,array("idstorage"=>$idstorage,));
    if (isset($storagemodel)) $urldata=array_merge($urldata,array("storagemodel"=>$storagemodel,));
    if (isset($scenario)) $urldata=array_merge($urldata,array("scenario"=>$scenario,));
    if (isset($idform)) $urldata=array_merge($urldata,array("idform"=>$idform,));
    if (count($urldata)>1) $this->renderPartial("/formview/_ff",array_merge($urldata,array("layouts"=>base64_encode("/cabinet/cabinet"),"addons"=>base64_encode('array("cabinetid"=>'.$cabinet->id.')'))));
}
// Узлы папки
$nodes=$folder->getItems("nodes");
if (count($nodes)==0) {
    return;
}
$nodeIds=array();
foreach ($nodes as $node) {
    $nodeIds=array_merge($nodeIds,array($node->id));
}
// Узлы формы (ИД допустимых узлов)
$availableNode=new FFModel;
$availableNode->registry=  FFModel::available_nodes;
$availableNode->refreshMetaData();
$availableNodeCriteria = new CDbCriteria();
$availableNodeCriteria->addInCondition("node", $nodeIds);
$availableNodes=$availableNode->findAll($availableNodeCriteria);
if (count($availableNodes)==0) {
    return;
}
$nodeIds=array();
foreach ($availableNodes as $node) {
    $nodeIds=array_merge($nodeIds,array($node->id));
}

// Определение форм
$refDocument=new FFModel;
$refDocument->registry=  FFModel::ref_multiguide;
$refDocument->refreshMetaData();
$refDocumentCriteria = new CDbCriteria();
$refDocumentCriteria->select="ref.`owner`";
$refDocumentCriteria->addInCondition("ref.`reference`", $nodeIds);
$refDocumentCriteria->alias = "ref";
$refDocuments=$refDocument->findAll($refDocumentCriteria);
$idDocuments=array();
foreach ($refDocuments as $refDocument) {
    $idDocuments=  array_merge($idDocuments, array($refDocument->owner));
}
$documents=FFModel::model()->findAllByPk($idDocuments);
// Теперь фильтруем по полю
$idDocuments=array();
$registryDocuments=array();
for ($index = 0; $index < count($documents); $index++) {
    $documents[$index]->refresh();
    $field=$documents[$index]->getField("available_nodes");
    if (isset($field) && $field!=NULL) {
        $idDocuments=  array_merge($idDocuments, array($documents[$index]->id));
        $registryDocuments=  array_merge($registryDocuments, array($documents[$index]->registry));
    }   
}
$registryDocuments=array_unique($registryDocuments,SORT_NUMERIC);
echo CHtml::hiddenField("folder_".$folder->id,count($idDocuments));
$documentCriteria = new CDbCriteria();
$documentCriteria->addInCondition("id", $idDocuments);
$model=new FFModel();
$commonP=FFModel::commonParent($registryDocuments);
$model->registry=  $commonP;
$model->refreshMetaData();
$dp=new CActiveDataProvider($model, 
                    array(
                        'criteria'=>$documentCriteria,
                        'pagination' => array(
                            'pageSize' => 50,
                            )
                        )
                    );
$columns = array(array('name'=>'id',"headerHtmlOptions"=>array("style"=>"width:60px"),'filter'=>''));
if (strlen($folder->getAttribute("visual_names"))>0) {
   $columnVisualNames = explode(";",$folder->visual_names);
   foreach ($columnVisualNames as $columnVisual) {
       if (trim($columnVisual)=="") continue;
       $columnVisualList=explode(":", $columnVisual);
       if (count($columnVisualList)==0) continue;
       $columnVisualName=$columnVisualList[0];
       if (count($columnVisualList)>1) $columnVisualTitle=$columnVisualList[1];
       else $columnVisualTitle=$columnVisualName;
       $columns = array_merge($columns,array(array('name'=>$columnVisualName,"header"=>$columnVisualTitle)));
   }
}

$columns = array_merge($columns, array(array('class'=>'CButtonColumn', "header"=>"Действия", 'buttons'=>$buttons)));
//echo '<pre>';
//var_dump($columns);
//echo '</pre>';
//return;
$this->widget("zii.widgets.grid.CGridView",
        array(
            "dataProvider"=>$dp, 
            "enablePagination"=>TRUE,
            'columns'=>$columns,
        ));
?>
<script type="text/javascript">
    $.ready($("#counter<?= $folder->id?>").html($("#folder_<?= $folder->id?>").val()));
</script>