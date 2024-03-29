<?php

class baseiceI18nAdminActions extends sfActions
{
  public function executeGetTargets(sfWebRequest $request)
  {
    $this->forward404If(!sfConfig::get('ice_i18n_enabled', false));
    
    $catalogue = $request->getParameter('catalogue');
    $source    = $request->getParameter('source');
    $pdo       = $this->getContext()->getI18n()->getMessageSource()->getConnection();
    
    $catalogues = $markers = $values = array();
    $cultures = sfConfig::get('app_ice_i18n_cultures_available');
    
    foreach($cultures as $code => $name)
    {
      $catalogues[] = $catalogue.'.'.$code; 
      $markers[] = '?';
      $values[] = $catalogue.'.'.$code;
    }
    
    $values[] = $source;
    
    $sql = sprintf("
      SELECT tc.name as tc_name, tc.source_lang as tc_culture, tu.target as tu_target
      FROM i18n_trans_unit tu
      LEFT JOIN i18n_catalogue as tc ON tc.cat_id = tu.cat_id
      WHERE 
        tc.name IN (%s) 
        AND tu.source = ?
    ", implode(', ', $markers));
    
    $stm = $pdo->prepare($sql);
    $stm->execute($values);

    $json = array();
    foreach($stm->fetchAll(PDO::FETCH_ASSOC) as $trans_unit)
    {
      $name_catalogue = $trans_unit['tc_name'];
      $culture = iceI18n::getLanguage($name_catalogue);
        
      $json['ice-i18n-target-'.$culture] = $trans_unit['tu_target'];
      unset($cultures[$culture]);
    }
    
    foreach($cultures as $code => $name)
    {
      $json['ice-i18n-target-'.$code] = '';
    }
    
    return $this->renderText(json_encode($json));
  }
  
  public function executeUpdateTargets(sfWebRequest $request)
  {

    $this->forward404If(!sfConfig::get('ice_i18n_enabled', false));
    
    $catalogue = $request->getParameter('catalogue');
    $source    = $request->getParameter('source');
    $targets   = $request->getParameter('targets');
    
    $params = array(
      'catalogue' => $catalogue,
      'source'    => $source,
      'targets'   => $targets
    );
    
    $form = new iceI18nTargetsForm(array(), array('message_source' => $this->context->getI18n()->getMessageSource()));

    $form->bind($params);
    if($form->isValid())
    {
      $form->save();

      // allow to tweak the clear_cache method
      $this->getContext()->getEventDispatcher()->notifyUntil(new sfEvent($params, 'iceI18n.clear_cache'));
    }
    
    return sfView::NONE;
  }

  public function executeGetMessagesByType(sfWebRequest $request)
  {
    $this->forward404If(!sfConfig::get('ice_i18n_enabled', false));
    
    $valid_types = array('ajax_lib_application', 'database');

    $pdo  = $this->getContext()->getI18n()->getMessageSource()->getConnection();
    $type = $request->getParameter('type');
    
    $this->forward404If(!in_array($type, $valid_types));
   
    $finder = sfFinder::type('file')
      ->ignore_version_control()
      ->prune('web')
      ->prune('test');

    $messages = array();
    
    if($type == 'ajax_lib_application')
    {
      // get ajax messages
      $event    = $this->getContext()->getEventDispatcher()->filter(new sfEvent($this, 'iceI18nPlugin.assign_ajax_values'), array());
      $ajax_messages = $event->isProcessed() ? $event->getReturnValue() : array();

      // get files messages
      $php_extractor = new iceI18nPhpExtractor;

      $in = sfConfig::get('sf_root_dir');
      $finder
        ->name('*actions.class.php')
        ->name('*.class.php')
      ;
      
      $files = $finder->in($in);
      foreach($files as $file)
      {
        $content  = file_get_contents($file);
        $messages = array_merge($messages, $php_extractor->extract($content));
      }

      $messages = array_merge($messages, $ajax_messages);
    }
    else if($type == 'database')
    {
      $message = '%'.$request->getParameter('message').'%';
      
      $stm = $pdo->prepare("
        SELECT DISTINCT tc.name tc_name, tu.target tu_target, tu.source tu_source
        FROM i18n_trans_unit tu
        LEFT JOIN i18n_catalogue tc ON tu.cat_id = tc.cat_id
        WHERE target LIKE ? OR source LIKE ?"
      );
      
      $stm->execute(array($message, $message));
      
      $messages = array();
      foreach($stm->fetchAll(PDO::FETCH_ASSOC) as $row)
      {
        
        $start = strpos($row['tc_name'], '.') + 1;
        $end   = strpos($row['tc_name'], '.', $start);
        $catalogue = substr($row['tc_name'], $start , $end - $start);

        $messages[] = array(
          'message' => $row['tu_source'],
          'catalogue' => $catalogue
        );
      }
    }

    // now transform the output to be valid
    $valid_messages = array();
    foreach($messages as $message)
    {
      if(!isset($message['message']))
      {
        // no message, .... error in parsing
        continue;
      }
      $original_catalogue = ($message['catalogue'] ? $message['catalogue'] : 'messages');
   
      $catalogue = $this->getContext()->getConfiguration()->getApplication().'.'.$original_catalogue;
      
      if(!array_key_exists($catalogue, $valid_messages))
      {
        $valid_messages[$catalogue] = array();
      }

      $this->getContext()->getConfiguration()->loadHelpers(array('Text'));

      $hash = md5($message['message']);
      $valid_messages[$catalogue][$hash] = array(
        'source' => $message['message'],
        'target' => truncate_text($this->context->getI18n()->__($message['message'], null, $original_catalogue), 70),
        'params' => isset($message['params']) ? $message['params'] : array(), // not fully implemented yet,
        'is_translated' => $message['message'] != $this->context->getI18n()->__($message['message'], null, $original_catalogue)
      );
    }

    return $this->renderText(json_encode(array(
      'type' => $type,
      'messages' => $valid_messages
    )));

  }
}