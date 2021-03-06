<?php
/**
 * @version     3.0.0
 * @package     com_imc
 * @subpackage  mod_imc
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
defined('_JEXEC') or die;

class ModImcfiltersHelper {
    
    private static $filters;

    public function getVotes($id) 
    {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT votes FROM #__imc_issues WHERE id='.$id);
        $votes = $db->loadResult();
        return $votes;
    }

    public static function getCategories($recursive = false)
    {
        $_categories = JCategories::getInstance('Imc');
        $_parent = $_categories->get();
        if(is_object($_parent))
        {
            $_items = $_parent->getChildren($recursive);
        }
        else
        {
            $_items = false;
        }
        return ModImcfiltersHelper::loadCats($_items);
    }
        
    private static function loadCats($cats = array())
    {
        if(is_array($cats))
        {
            $i = 0;
            $return = array();
            foreach($cats as $JCatNode)
            {
                $return[$i] = new stdClass();
                $return[$i]->title = $JCatNode->title;
                $return[$i]->parentid = $JCatNode->parent_id;
                $return[$i]->path = $JCatNode->get('path');
                $return[$i]->id = $JCatNode->id;
                //$params = new JRegistry();
                //$params->loadJSON($JCatNode->params);
                //$return[$i]->image = $params->get('image');

                if($JCatNode->hasChildren())
                    $return[$i]->children = ModImcfiltersHelper::loadCats($JCatNode->getChildren());
                else
                    $return[$i]->children = false;

                $i++;
            }
            return $return;
        }
        return false;
    }

    private static function createFilters($cats = array())
    {
        $app = JFactory::getApplication();
        $filter_category = $app->getUserStateFromRequest('com_imc.pois.filter.category', 'cat', array());
    
        self::$filters .= '<ul class="imc_ulist">';
        foreach($cats as $JCatNode){
            //id is the category id
            if(empty($filter_category)){
                if($JCatNode->parentid == 'root')       
                    self::$filters .='<li><input path="'.$JCatNode->path.'" parent="box'.$JCatNode->parentid.'" name="cat[]" value="'.$JCatNode->id.'" type="checkbox" checked="checked" id="cat-'.$JCatNode->id.'" onclick="imc_filterbox_click(this,'.$JCatNode->id.')" /><span class="root">'.' '.$JCatNode->title.'</span></li>' . "\n";
                else
                    self::$filters .='<li><input path="'.$JCatNode->path.'" parent="box'.$JCatNode->parentid.'" name="cat[]" value="'.$JCatNode->id.'" type="checkbox" checked="checked" id="cat-'.$JCatNode->id.'" onclick="imc_filterbox_click(this,'.$JCatNode->id.')" />'. ' ' .$JCatNode->title.'</li>' . "\n";
            }
            else{
                if($JCatNode->parentid == 'root'){
                    self::$filters .='<li><input path="'.$JCatNode->path.'" parent="box'.$JCatNode->parentid.'" name="cat[]" value="'.$JCatNode->id.'" type="checkbox" '; if(in_array($JCatNode->id, $filter_category)) self::$filters .= 'checked="checked"'; self::$filters .= ' id="cat-'.$JCatNode->id.'" onclick="imc_filterbox_click(this,'.$JCatNode->id.')" /><span class="root">'.' '.$JCatNode->title.'</span></li>' . "\n";
                }
                else{
                    self::$filters .='<li><input path="'.$JCatNode->path.'" parent="box'.$JCatNode->parentid.'" name="cat[]" value="'.$JCatNode->id.'" type="checkbox" '; if(in_array($JCatNode->id, $filter_category)) self::$filters .= 'checked="checked"'; self::$filters .= ' id="cat-'.$JCatNode->id.'" onclick="imc_filterbox_click(this,'.$JCatNode->id.')" />'.' '.$JCatNode->title.'</li>' . "\n";
                }   
            }
            
            if(!empty($JCatNode->children)){
                ModImcfiltersHelper::createFilters($JCatNode->children);
            }
        
        }
        self::$filters .= '</ul>';

        return self::$filters;
    }

    private static function createFiltersAsArray($cats)
    {
        $ar[] = null;
        foreach($cats as $cat){
            self::$filters = '';
            $ar[] = ModImcfiltersHelper::createFilters(array($cat));
        }
        self::$filters = '';
        return $ar;
    }

    public function createLimitBox($lim)
    {
        // Initialise variables.
        $app = JFactory::getApplication();
        //$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $lim);
        $selected = $limit;

        $html = '';
        $values = array (1, 5, 10, 20, 100, 0);
        foreach($values as $i){
            $a = $i;
            if($a == 0)
                $a = JText::_('JALL');
            if($selected == $i){
                $html .= '<li><a href="#" onclick="jQuery(\'input[name=limit]\').val('.$i.');jQuery(\'#adminForm\').submit();">'.$a.' <i class="icon-ok"></i></a></li>';
            }
            else {
                $html .= '<li><a href="#" onclick="jQuery(\'input[name=limit]\').val('.$i.');jQuery(\'#adminForm\').submit();">'.$a.'</a></li>';
            }
        }
        return $html;
    }

    public static function getCategoryFilters($cat_id = 0) {
        $categories = ModImcfiltersHelper::getCategories();
        return ModImcfiltersHelper::createFiltersAsArray($categories);
        /*
        $categories = ModImcfiltersHelper::getCategories();
        $tree = array();
        foreach ($categories as $row) {
            if($row['parent_id'] == 1) {
                $row['Children'] = array();
                $tree[$row['id']] = $row;
            } else {
                $tree[$row['parent_id']]['Children'][] = $row;
            }       
        }
        */
    
        /*       
        $tree = array();
        $categories = JCategories::getInstance('Imc');
        $cat = $categories->get($cat_id);
        $children = $cat->getChildren(); 
        foreach ($children as $child) {
            //echo ':'.$child->title . '<br />';
            $tree[$child->id] = $child->title;
            ModImcfiltersHelper::createCategoryFilters($child->id);
        }

        return $tree;
        */

        /*
        return JHtmlCategory::options('com_imc');
        */
    }

   
}
