/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2009 Thomas Rabaix <thomas.rabaix@soleoweb.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    iceI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
function iceI18nPlugin(options)
{

  this.url_translation = null;
  this.url_messages    = null;

  this.hide_translated = false;

  this.page = {
    loaded: false,
    messages: {},
    panel: null
  }

  this.ajax_lib_application = {
    loaded: false,
    messages: {},
    panel: null
  }

  this.database = {
    loaded: false,
    messages: {},
    panel: null
  }
  
  this.init(options);
}

iceI18nPlugin.state    = {
  mode: 'none',
  dragged: false
}

iceI18nPlugin.instance = null;

iceI18nPlugin.prototype.displayLoading = function(mode)
{
  if(mode == 'show')
  {
    jQuery('#ice-i18n-left-box').hide();
    jQuery('#ice-i18n-right-box').hide();
    jQuery('#ice-i18n-loading-box').show();

  }
  else
  {
    jQuery('#ice-i18n-left-box').show();
    jQuery('#ice-i18n-right-box').show();
    jQuery('#ice-i18n-loading-box').hide();
  }
}

iceI18nPlugin.prototype.init = function(options)
{

  this.url_translation = options.url_translation || null;
  this.url_messages = options.url_messages || null;
  
  this.modal = jQuery('#ice-i18n-dialog');
  
  // create the dialog box
  jQuery('#ice-i18n-dialog').draggable({
    appendTo: 'body',
    zIndex: 10000,
    handle: 'h2',
    start: function(event, ui) {

    },
    stop: function(event, ui) {
      iceI18nPlugin.state.dragged = true;

      jQuery(this).css('zIndex', 10000);
    }
  });

  jQuery('#ice-i18n-on-top-box', this.modal).click(function(){

    if(iceI18nPlugin.state.dragged)
    {
      iceI18nPlugin.state.dragged = false;
      return;
    }

    iceI18nPlugin.instance.toggleModalState(
      jQuery('#ice-i18n-left-box').css('display') == 'none' ? 'show' : 'hide'
    );
  });

  // create the tabulation
  jQuery('#ice-i18n-left-box', this.modal).tabs({
    select: function(event, ui) {

      var rel = ui.panel.getAttribute('rel');

      if(!rel)
      {

        return;
      }

      if(iceI18nPlugin.instance[rel].loaded == true)
      {

        return;
      }

      jQuery.ajax({
        type: 'GET',
        url: iceI18nPlugin.instance.url_messages.replace('MESSAGE_TYPE', rel),
        data: {},
        dataType: "json",
        cache: false,
        success: function(data, textStatus) {
          var type = data.type;
          var messages = data.messages;
          iceI18nPlugin.instance.loadTranslationTable(type, messages);
        }
      });

      iceI18nPlugin.instance.displayLoading('show');

    }
  });

  // handle the translation form
  jQuery('#ice-i18n-form-update', this.modal).submit(function(event) {

    event.preventDefault();

    jQuery('#ice-i18n-loading').show();
    jQuery('#ice-i18n-submit').hide();

    jQuery.ajax({
      type: 'GET',
      url: jQuery('#ice-i18n-form-update').attr('action'),
      data: jQuery("#ice-i18n-form-update").serialize(),
      cache: false,
      success: function(data, textStatus) {
        jQuery('#ice-i18n-loading').hide();
        jQuery('#ice-i18n-submit').show();
      }
    })

    return false;
  });

  // handle hide translation checkbox
  jQuery('input.ice-i18n-hide-translated', this.modal).change(function() {

    var panel = jQuery(this).parent().parent();
    var display = jQuery(this).attr('checked');

    iceI18nPlugin.instance.displayTranslated(panel, display);
  });

  jQuery('input.ice-i18n-current-page-search', this.modal).keyup(function() {

    var panel = jQuery(this).parent().parent();
    var value = jQuery(this).val();

    iceI18nPlugin.instance.filterTranslated(panel, value);
  });

  jQuery('input.ice-i18n-current-database-search', this.modal).keyup(function(event){
    if(event.keyCode == 13)
    {
      jQuery('tbody', iceI18nPlugin.instance.database.panel).html('');
      
      jQuery.ajax({
        type: 'GET',
        url: iceI18nPlugin.instance.url_messages.replace('MESSAGE_TYPE', 'database'),
        data: {message: jQuery(this).val()},
        dataType: "json",
        cache: false,
        success: function(data, textStatus) {
          var type = data.type;
          var messages = data.messages;
          iceI18nPlugin.instance.loadTranslationTable(type, messages);
        }
      });
    }
  });
  
  this.page.panel     = jQuery('div#ice-i18n-panel-page', this.modal);
  this.ajax_lib_application.panel = jQuery('div#ice-i18n-panel-ajax_lib_application', this.modal);
  this.database.panel = jQuery('div#ice-i18n-panel-database', this.modal);
  
  jQuery('#ice-i18n-loading', this.modal).hide();
  jQuery('#ice-i18n-submit', this.modal).hide();
  jQuery('.ice-i18n-parameters', this.modal).hide();
  jQuery('#ice-i18n-dialog', this.modal).resizable();

  this.toggleModalState('hide');
}

iceI18nPlugin.prototype.toggleModalState = function(mode)
{

  jQuery('#ice-i18n-dialog').show();
  
  if(mode == 'show')
  {
    jQuery('#ice-i18n-dialog')
      .fadeTo(0, 1)
      .animate({width: '700px', heigth: '400px'}, 500)
      .css('zIndex', 10000)
    ;

    jQuery('#ice-i18n-left-box').show();
    jQuery('#ice-i18n-right-box').show();

    this.loadTranslationTable('page', _ice_i18n_messages);
    
  }
  else
  {
    jQuery('#ice-i18n-dialog').css('height', null);
    jQuery('#ice-i18n-dialog').css('width', 100);
    jQuery('#ice-i18n-dialog').fadeTo(0, 0.25)

    jQuery('#ice-i18n-left-box').hide();
    jQuery('#ice-i18n-right-box').hide();
    jQuery('#ice-i18n-loading-box').hide();
  }

}

iceI18nPlugin.prototype.displayTranslated = function(panel, display)
{
  this.hide_translated = display;

  if(this.hide_translated === true)
  {
    jQuery('tr.ice-target-translated', panel).hide();
  }
  else
  {
    jQuery('tr', panel).show()
  }

  this.filterTranslated(panel, jQuery('input.ice-i18n-current-page-search', panel).val());
}

iceI18nPlugin.prototype.filterTranslated = function(panel, value)
{

  jQuery('tr', panel).hide();

  var re = new RegExp(value, 'ig');

  jQuery('tr', panel).each(function() {
    var match = false;
    jQuery('td', this).each(function() {
      if(jQuery(this).html().match(re))
      {
        match = true;
      }

      return;
    })

    if(match)
    {
      if(iceI18nPlugin.instance.hide_translated && jQuery(this).hasClass('ice-target-translated'))
      {
        return;
      }

      jQuery(this).show();
    }
  });
}

iceI18nPlugin.prototype.loadTranslationTable = function(name, ice_i18n_messages)
{

  if(this[name].loaded)
  {
    return;
  }

  this[name].messages = ice_i18n_messages;
  this[name].loaded   = true;

  if(name == 'database')
  {
    this[name].loaded = false;
  }
  
  var tbody = jQuery('tbody', this[name].panel);

  var html = "";

  var current_catalogue = '';
  for(name_catalogue in this[name].messages)
  {
    
    html += "<tr><td colspan='2' style='font-weight:bold; padding-top:10px'>" + name_catalogue + "</td></tr>";
    var catalogue = this[name].messages[name_catalogue];
    var display_catalogue = name_catalogue.split(".")[1];

    for(index in catalogue)
    {
      trans = catalogue[index];

      html += "<tr catalogue='" + name_catalogue + "' rel='" + name + "' class='_mg_i18_td_unselected " + (trans.is_translated ? 'ice-target-translated' : 'ice-target-non-translated') + "'>";
      html += "  <td hash='" + index + "'>"  + trans.target + "</td>";
      html += "  <td><em class='source'>" + trans.source + "</em></td>";
      html += "</tr>";
    }
  }

  tbody.append(html);

  jQuery('tr', tbody)
    .mouseover(function() {
      jQuery(this).css('cursor', 'pointer')
    })
    .click(function() {
      var tr = jQuery(this);
      var panel =  iceI18nPlugin.instance[tr.attr('rel')];
      var tds = jQuery('td', this);

      jQuery('td', tbody)
        .removeClass('_mg_i18_td_selected')
        .addClass('_mg_i18_td_unselected');

      tds
        .removeClass('_mg_i18_td_unselected')
        .addClass('_mg_i18_td_selected');

      // toggle the loading icon
      jQuery('#ice-i18n-loading').show();
      jQuery('#ice-i18n-submit').hide();

      // clear the form
      jQuery('input[type=text]', '#ice-i18n-form-update').val('');
      jQuery('textarea', '#ice-i18n-form-update').val('');

      var catalogue = tr.attr('catalogue');
      var source    = jQuery('em.source', tr).html();
      var hash      = jQuery(tds.get(0)).attr('hash');
      var i18n_params     = panel.messages[catalogue][hash]['params'];

      // set variables and submit form to get the variable
      jQuery('#ice-i18n-catalogue').val(catalogue);
      jQuery('#ice-i18n-source').val(source);

      if(i18n_params && i18n_params.length > 0)
      {
        jQuery('.ice-i18n-parameters').show();
        jQuery('#ice-i18n-parameters-text').html(i18n_params);
      }
      else
      {
        jQuery('.ice-i18n-parameters').hide();
      }

      jQuery.ajax({
        type: 'GET',
        url: iceI18nPlugin.instance.url_translation,
        dataType: "json",
        data: jQuery("#ice-i18n-form-update").serialize(),
        cache: false,
        success: function(data, textStatus) {
          for(var param in data) {
            jQuery('#' + param, "#ice-i18n-form-update").val(data[param]);
          }

          jQuery('#ice-i18n-loading').hide();
          jQuery('#ice-i18n-submit').show();
        }
      })
    });

  this.displayLoading('hide');
}