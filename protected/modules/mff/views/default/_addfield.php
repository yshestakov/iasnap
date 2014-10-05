<?php
//$cs=Yii::app()->getClientScript(); $cs->registerCoreScript('jquery'); $cs->registerCoreScript('yii');
    $fieldmodel = new FFField();
    CActiveForm::validate($fieldmodel);
    $fieldmodel->formid = $formid;
    $target_saveaddfield = "$.post('".$this->createUrl('default/fieldnew',array("formid"=>$formid))."',formaddfield); $(this).dialog('close');";
    
    $this->beginWidget("zii.widgets.jui.CJuiDialog",
            array( 'options' => 
                array(
                    'title' => 'Добавление поля',
                    'modal' => true,
                    'resizable'=> true,
                    'autoOpen'=>false,
                    'width'=>"45%",
                    'buttons' => array(
                        array('text'=>'Сохранить','type' => 'submit','click'=>'js:function(){formaddfield.submit(); return true;}'), // 'click'=> 'js:function(){$.yii.submitForm(this,"","");}'
                        array('text'=>'Отменить','click'=> 'js:function(){$(this).dialog("close");}'),
                    )
                ),
                'id' => 'frmaddfield',
             )
    );
    $formaddfield=$this->beginWidget("CActiveForm", array(
        'id'=>'formaddfield',
        'enableAjaxValidation' => true,
        'action'=>$this->createUrl('default/fieldnew',array("formid"=>$formid)),
        'clientOptions' => array(
            'validateOnChange'=>true,           
            ),
        )
    );

    
    echo $formaddfield->hiddenField($fieldmodel,"formid");

    ?>
<table style="width: 100%">
    <tr>
        <td><?php echo $formaddfield->labelEx($fieldmodel,"name"); ?></td>
        <td><?php echo $formaddfield->textField($fieldmodel,'name'); ?></td>
    </tr>
    <tr>
        <td colspan="2"><?php echo $formaddfield->error($fieldmodel,'name'); ?></td>
    </tr>
    <tr>
        <td><?php echo $formaddfield->labelEx($fieldmodel,"type"); ?></td>
        <td><?php 
            $listdata =  array_merge(array(""=>""), CHtml::listData(FFTypes::model()->findAll(), "id", "typename"));
            echo $formaddfield->dropDownList($fieldmodel, "type", $listdata);
        ?></td>
    </tr>
    <tr>
        <td colspan="2"><?php echo $formaddfield->error($fieldmodel,'type'); ?></td>
    </tr>
    <tr>
        <td><?php echo $formaddfield->labelEx($fieldmodel,"order"); ?></td>
        <td><?php echo $formaddfield->textField($fieldmodel,"order"); ?></td>
    </tr>
    <tr>
        <td colspan="2"><?php echo $formaddfield->error($fieldmodel,'order'); ?></td>
    </tr>
    <tr>
        <td><?php echo $formaddfield->labelEx($fieldmodel,"description"); ?></td>
        <td><?php echo $formaddfield->textArea($fieldmodel,"description"); ?></td>
    </tr>
    <tr>
        <td colspan="2"><?php echo $formaddfield->error($fieldmodel,'description'); ?></td>
    </tr>
</table>
<?php
    $this->endWidget("CActiveForm");
    $this->endWidget("zii.widgets.jui.CJuiDialog");


