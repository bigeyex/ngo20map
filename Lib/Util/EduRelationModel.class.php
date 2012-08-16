<?php

class EduRelationModel extends RelationModel{

    protected function getRelation(&$result,$name='',$return=false)
    {
        if(!empty($this->_link)) {
            foreach($this->_link as $key=>$val) {
                    $mappingName =  !empty($val['mapping_name'])?$val['mapping_name']:$key; // 映射名称
                    if(empty($name) || true === $name || $mappingName == $name || (is_array($name) && in_array($mappingName,$name))) {
                        $mappingType = !empty($val['mapping_type'])?$val['mapping_type']:$val;  //  关联类型
                        $mappingClass  = !empty($val['class_name'])?$val['class_name']:$key;            //  关联类名
                        $mappingFields = !empty($val['mapping_fields'])?$val['mapping_fields']:'*';     // 映射字段
                        $mappingCondition = !empty($val['condition'])?$val['condition']:'1=1';          // 关联条件
                        if(strtoupper($mappingClass)==strtoupper($this->name)) {
                            // 自引用关联 获取父键名
                            $mappingFk   =   !empty($val['parent_key'])? $val['parent_key'] : 'parent_id';
                        }else{
                            $mappingFk   =   !empty($val['foreign_key'])?$val['foreign_key']:strtolower($this->name).'_id';     //  关联外键
                        }
                        // 获取关联模型对象
                        $model = M($mappingClass);
                        switch($mappingType) {
                            case HAS_ONE:
                                $pk   =  $result[$this->getPk()];
                                $mappingCondition .= " AND {$mappingFk}='{$pk}'";
                                $relationData   =  $model->where($mappingCondition)->field($mappingFields)->find();
                                break;
                            case BELONGS_TO:
                                if(strtoupper($mappingClass)==strtoupper($this->name)) {
                                    // 自引用关联 获取父键名
                                    $mappingFk   =   !empty($val['parent_key'])? $val['parent_key'] : 'parent_id';
                                }else{
                                    $mappingFk   =   !empty($val['foreign_key'])?$val['foreign_key']:strtolower($model->getModelName()).'_id';     //  关联外键
                                }
                                $fk   =  $result[$mappingFk];
                                $mappingCondition .= " AND {$model->getPk()}='{$fk}'";
                                $relationData   =  $model->where($mappingCondition)->field($mappingFields)->find();
                                break;
                            case HAS_MANY:
                                $pk   =  $result[$this->getPk()];
                                $mappingCondition .= " AND {$mappingFk}='{$pk}'";
                                $mappingOrder =  !empty($val['mapping_order'])?$val['mapping_order']:'';
                                $mappingLimit =  !empty($val['mapping_limit'])?$val['mapping_limit']:'';
                                // 延时获取关联记录
                                $relationData   =  $model->where($mappingCondition)->field($mappingFields)->order($mappingOrder)->limit($mappingLimit)->select();
                                break;
                            case MANY_TO_MANY:
                                $pk   =  $result[$this->getPk()];
                                $mappingCondition = " {$mappingFk}='{$pk}'";
                                $mappingOrder =  $val['mapping_order'];
                                $mappingLimit =  $val['mapping_limit'];
                                $mappingRelationFk = $val['relation_foreign_key']?$val['relation_foreign_key']:$model->getModelName().'_id';
                                $mappingRelationTable  =  $val['relation_table']?$val['relation_table']:$this->getRelationTableName($model);
                                $sql = "SELECT a.*,b.{$mappingFields} FROM {$mappingRelationTable} AS a, ".$model->getTableName()." AS b WHERE a.{$mappingRelationFk} = b.{$model->getPk()} AND a.{$mappingCondition}";
                                if(!empty($val['condition'])) {
                                    $sql   .= ' AND '.$val['condition'];
                                }
                                if(!empty($mappingOrder)) {
                                    $sql .= ' ORDER BY '.$mappingOrder;
                                }
                                if(!empty($mappingLimit)) {
                                    $sql .= ' LIMIT '.$mappingLimit;
                                }
                                $relationData   =   $this->query($sql);
                                break;
                        }
                        if(!$return){
                            if(isset($val['as_fields']) && in_array($mappingType,array(HAS_ONE,BELONGS_TO)) ) {
                                // 支持直接把关联的字段值映射成数据对象中的某个字段
                                // 仅仅支持HAS_ONE BELONGS_TO
                                $fields =   explode(',',$val['as_fields']);
                                foreach ($fields as $field){
                                    if(strpos($field,':')) {
                                        list($name,$nick) = explode(':',$field);
                                        $result[$nick]  =  $relationData[$name];
                                    }else{
                                        $result[$field]  =  $relationData[$field];
                                    }
                                }
                            }else{
                                $result[$mappingName] = $relationData;
                            }
                            unset($relationData);
                        }else{
                            return $relationData;
                        }
                    }
            }
        }
        return $result;
    }

}

?>
