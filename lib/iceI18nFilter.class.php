<?php

class iceI18nFilter extends sfFilter
{
  /**
   * Executes this filter.
   *
   * @param sfFilterChain $filterChain A sfFilterChain instance
   */
  public function execute($filterChain)
  {
    // execute next filter
    $filterChain->execute();

    if (!sfConfig::get('ice_i18n_enabled', false))
    {
      //return;
    }

    // execute this filter only once
    $response = $this->context->getResponse();

    // include javascripts and stylesheets
    $content = $response->getContent();

    if (false !== ($pos = strpos($content, '</body>')))
    {
      // preload required helper
      $this->context->getConfiguration()->loadHelpers(array('Partial', 'I18N'));
      
      $html = '';
      $html .=  get_component('iceI18nAdmin', 'displayTranslationBox');
      $html .= "<script type='text/javascript'>\n";
      $html .= "\tvar _ice_i18n_messages = ".json_encode($this->context->getI18n()->getRequestedMessages());
      $html .= "\n</script>\n";

      $response->setContent(substr($content, 0, $pos).$html.substr($content, $pos));
    }
  }
}
