<?php
/**
 * @version     3.0.0
 * @package     com_imc
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU AFFERO GENERAL PUBLIC LICENSE Version 3; see LICENSE
 * @author      Ioannis Tsampoulatidis <tsampoulatidis@gmail.com> - https://github.com/itsam
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

$user = JFactory::getUser();
$userId = $user->get('id');

// $canEdit = $user->authorise('core.edit', 'com_imc');
// $canDelete = $user->authorise('core.delete', 'com_imc');
?>

<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function() {
        var container = document.querySelector('.masonry');
        var msnry = new Masonry( container, {
          // options
          //columnWidth: 70,
          itemSelector: '.masonry-element'
        });

        imagesLoaded( container, function() {
          msnry.layout();
        });
    });
</script>

<div id="columns">
    <div class="row masonry" id="masonry-sample">
        <?php foreach ($this->items as $i => $item) : ?>
            <?php
                $canCreate = $user->authorise('core.create', 'com_imc.issue.'.$item->id);
                $canEdit = $user->authorise('core.edit', 'com_imc.issue.'.$item->id);
                $canCheckin = $user->authorise('core.manage', 'com_imc.issue.'.$item->id);
                $canChange = $user->authorise('core.edit.state', 'com_imc.issue.'.$item->id);
                $canDelete = $user->authorise('core.delete', 'com_imc.issue.'.$item->id);
                $canEditOwn = $user->authorise('core.edit.own', 'com_imc.issue.' . $item->id);
                $photos = json_decode($item->photo);
            ?>
            <?php if (!$canEdit && $user->authorise('core.edit.own', 'com_imc.issue.'.$item->id)): ?>
                <?php $canEdit = JFactory::getUser()->id == $item->created_by; ?>
            <?php endif; ?>
                
            <div class="col-sm-6 col-md-4 col-xs-12 masonry-element">
                <div id="imc-panel-<?php echo $item->id;?>" class="panel panel-default">
                    <?php if (JFactory::getUser()->id == $item->created_by) : ?>  
                      <div class="ribbon-wrapper-corner"><div class="ribbon-corner"><?php echo JText::_('COM_IMC_ISSUES_MY_ISSUE');?></div></div>
                    <?php else : ?>
                        <?php if($item->votes > 0) : ?>
                        <div title="<?php echo JText::_('COM_IMC_ISSUES_VOTES');?>" class="book-ribbon">
                            <div>+<?php echo $item->votes; ?></div>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if(empty($photos->files) || !file_exists($photos->imagedir .'/'. $photos->id . '/medium/' . (@$photos->files[0]->name))) : ?>
                        <!-- <img src="//placehold.it/450X300/OO77BB/ffffff" class="img-responsive"> -->
                    <?php else : ?>
                        <div class="panel-thumbnail">
                            <img src="<?php echo $photos->imagedir .'/'. $photos->id . '/medium/' . ($photos->files[0]->name) ;?>" alt="issue photo" class="img-responsive" />
                        </div>
                    <?php endif; ?>

                    <div class="<?php echo ($item->state == 0 ? 'issue-unpublished ' : ''); ?>panel-body">
                        <p class="lead">
                            <?php if($item->category_image != '') : ?>
                            <img src="<?php echo $item->category_image; ?>" alt="category image" />
                            <?php endif; ?>
                            <?php if ($canEdit) : ?>
                              <a href="<?php echo JRoute::_('index.php?option=com_imc&task=issue.edit&id='.(int) $item->id); ?>">
                              <i class="icon-edit"></i> <?php echo $this->escape($item->title); ?></a>
                            <?php else : ?>
                              <?php echo $this->escape($item->title); ?>
                            <?php endif; ?>
                            <?php if (isset($item->checked_out) && $item->checked_out) : ?>
                              <i class="icon-lock"></i> <?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'issues.', $canCheckin); ?>
                            <?php endif; ?>
                        </p>

                        <?php if($item->updated == $item->created) : ?>
                        <span class="label label-default" title="<?php echo JText::_('COM_IMC_ISSUES_CREATED');?>"><?php echo ImcFrontendHelper::getRelativeTime($item->created); ?></span>
                        <?php else : ?>
                        <span class="label label-default" title="<?php echo JText::_('COM_IMC_ISSUES_UPDATED');?>"><?php echo ImcFrontendHelper::getRelativeTime($item->updated); ?></span>
                        <?php endif; ?>
                        <span class="label label-info" style="background-color: <?php echo $item->stepid_color;?>" title="<?php echo JText::_('COM_IMC_ISSUES_STEPID');?>"><?php echo $item->stepid_title; ?></span>
                        <span class="label label-default" title="<?php echo JText::_('COM_IMC_ISSUES_CATID');?>"><?php echo $item->catid_title; ?></span>
                        <span class="label label-default" title="<?php echo JText::_('COM_IMC_TITLE_COMMENTS');?>"><i class="icon-comment"></i> 0</span>
                        <?php if (JFactory::getUser()->id == $item->created_by && $item->votes > 0) : ?>
                        <span class="label label-default" title="<?php echo JText::_('COM_IMC_ISSUES_VOTES');?>">+<?php echo $item->votes; ?></span>
                        <?php endif; ?>

                        <p><?php echo ImcFrontendHelper::cutString($item->description, 200); ?></p>

                        <p><a href="<?php echo JRoute::_('index.php?option=com_imc&view=issue&id='.(int) $item->id); ?>"><?php echo JText::_('COM_IMC_ISSUES_MORE');?></a></p>
                        <?php if($item->state == 0) : ?>
                            <hr />
                            <p class="imc-warning"><i class="icon-info-sign"></i> <?php echo JText::_('COM_IMC_ISSUES_NOT_YET_PUBLISHED');?></p>
                        <?php endif; ?>
                    </div>
                </div><!-- /imc-panel-X -->
            </div><!--/col--> 
        <?php endforeach; ?>
    </div>
</div><!-- /columns -->

<?php //echo $this->pagination->getListFooter(); ?>