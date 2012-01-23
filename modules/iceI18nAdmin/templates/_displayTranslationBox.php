
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo $sf_request->getRelativeUrlRoot() ?>/iceI18nPlugin/css/redmond-jquery-ui.css" />
<script type="text/javascript" src="<?php echo $sf_request->getRelativeUrlRoot() ?>/iceI18nPlugin/js/gui.js" ></script>

<div id="ice-i18n-dialog" class="ui-widget">
  <div id="ice-i18n-on-top-box">
    <h2 class="ui-widget-header ui-corner-all"><?php echo __('title_translation', array(), 'iceI18nAdmin') ?></h2>
  </div>

  <div id="ice-i18n-container" class="ui-widget ui-widget-content ui-corner-all">
    <div id="ice-i18n-left-box">
      <ul>
        <li><a href="#ice-i18n-panel-page"><?php echo __('tabs_translation_current_page', array(), 'iceI18nAdmin') ?></a></li>
        <li><a href="#ice-i18n-panel-ajax_lib_application"><?php echo __('tabs_translation_ajax_lib_application', array(), 'iceI18nAdmin') ?></a></li>
        <li><a href="#ice-i18n-panel-database"><?php echo __('tabs_translation_db', array(), 'iceI18nAdmin') ?></a></li>
      </ul>

      <?php foreach(array('page', 'ajax_lib_application') as $type): ?>
        <div id="ice-i18n-panel-<?php echo $type ?>" rel="<?php echo $type ?>">
          <div class="ice-i18n-toolbar">
            <input type="checkbox" class="ice-i18n-hide-translated" />
            <label for="ice-18n-current-page-hide-translated"><?php echo __('label_hide_translated_page', array(), 'iceI18nAdmin') ?></label>

            <?php echo __('label_filter_list', array(), 'iceI18nAdmin') ?>
            <input type="text" class="ice-i18n-current-page-search" />
          </div>
          <div class="ice-i18n-messages">
            <table>
              <thead>
                <tr>
                  <td class="ice-i18n-td-catalogue"><?php echo __('header_target', array(), 'iceI18nAdmin') ?></td>
                  <td class="ice-i18n-td-targets"><?php echo __('header_source', array(), 'iceI18nAdmin') ?></td>
                </tr>
              </thead>
              <tbody />
              <tfoot />
            </table>
          </div>
        </div>
      <?php endforeach ?>
      
      <div id="ice-i18n-panel-database">
        <div class="ice-i18n-toolbar">
          <?php echo __('label_filter_list', array(), 'iceI18nAdmin') ?>
          <input type="text" class="ice-i18n-current-database-search" />
        </div>
        <div class="ice-i18n-messages">
          <table>
            <thead>
              <tr>
                <td class="ice-i18n-td-catalogue"><?php echo __('header_catalogue', array(), 'iceI18nAdmin') ?></td>
                <td class="ice-i18n-td-targets"><?php echo __('header_targets', array(), 'iceI18nAdmin') ?></td>
              </tr>
            </thead>
            <tbody />
            <tfoot />
          </table>
        </div>
      </div>
    </div>

    <div id="ice-i18n-right-box">
      <strong class="ice-i18n-parameters"><?php echo __('label_parameters', array(), 'iceI18nAdmin') ?></strong>
      <span class="ice-i18n-parameters" id="ice-i18n-parameters-text"></span>
      <br class="ice-i18n-parameters" /><br class="ice-i18n-parameters" />

      <form action="<?php echo url_for('@ice_i18n_update') ?>" id="ice-i18n-form-update">
        <input type="hidden" readonly="true" name="catalogue" id="ice-i18n-catalogue" value="" />
        <input type="hidden" readonly="true" name="source" id="ice-i18n-source" value="" />

        <div class="ice-i18-translations">
          <?php foreach(sfConfig::get('app_ice_i18n_cultures_available') as $code => $name): ?>
            <div class="ice-i18n-translation">
              <strong><?php echo $name ?></strong><br />
              <textarea class="ice-i18n-translation-input" name='targets[<?php echo $code ?>]' id='ice-i18n-target-<?php echo $code ?>'></textarea>
            </div>
          <?php endforeach; ?>
        </div>

        <div>
          <img src="<?php echo $sf_request->getRelativeUrlRoot() ?>/iceI18nPlugin/images/tiny_red.gif" id="ice-i18n-loading"/>
          <input type="submit" value="<?php echo __('btn_save_translation', array(), 'iceI18nAdmin') ?>" id="ice-i18n-submit" />
        </div>
      </form>
    </div>
    <div style="clear:both"></div>
  </div>

  <div id="ice-i18n-loading-box" class="ui-widget ui-widget-content ui-corner-all">
    <?php echo __('message_loading', array(), 'iceI18nAdmin') ?> <br />
    <img src="<?php echo $sf_request->getRelativeUrlRoot() ?>/iceI18nPlugin/images/tiny_red.gif"/>
  </div>
</div>

<script type="text/javascript">
  if(typeof jQuery != 'undefined')
  {
    jQuery(window).bind('load', function() {
      iceI18nPlugin.instance = new iceI18nPlugin({
        url_translation: '<?php echo url_for('ice_i18n_get_targets') ?>',
        url_messages: '<?php echo url_for('@ice_i18n_get_messages?type=MESSAGE_TYPE') ?>'
      });
    });
  }
  else
  {
    alert('Please add jQuery UI to see the translation tools');
  }
</script>
