<?php
/**
 * Created by JetBrains PhpStorm.
 * User: thomas
 * Date: 04.07.13
 * Time: 09:20
 * To change this template use File | Settings | File Templates.
 */

// http://framework.zend.com/manual/1.12/de/zend.paginator.advanced.html#zend.paginator.advanced.adapters

class SphinxSearch_ObjectList extends SphinxSearch_ListAbstract {

  public function current() {
    $this->load();
    $id = $this->result_ids[$this->pointer];
    $objectString = "Object_".ucfirst($this->class_name);
    $object = $objectString::getById($id);
    return $object;
  }

  public function load($override = false) {
    if ($this->search_result_items !== null && !$override) {
      return $this->search_result_items;
    }
    $search_result = $this->getObjectIDs();
    $sliced = array_slice($search_result, $this->offset, $this->limit, true);

    $objectString = "Object_".ucfirst($this->class_name);
    $entries = array();
    foreach ($sliced as $id => $meta) {
      $entries[] = $objectString::getById($id);
    }
    return $this->search_result_items = $entries;
  }

  public function getTotalCount() {
    return count($this->getObjectIds());
  }

  private function getObjectIds() {
    if ($this->search_result_ids === null) {
      $index = "idx_".strtolower($this->class_name);
      $object_class = Object_Class::getByName($this->class_name);
      if($object_class->getFieldDefinition("localizedfields")) {
        $locale = Zend_Registry::get("Zend_Locale");
        $language = $locale->getLanguage();
        $index .= "_".$language;
      }

      $search_result = $this->SphinxClient->Query($this->query, $index);
      if ($search_result === false ) {
        throw new Exception($this->SphinxClient->GetLastError()."\n query:".$this->query);
      }
      $this->search_result_ids = $search_result["matches"];
    }
    return $this->search_result_ids;
  }


}