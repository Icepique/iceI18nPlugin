<?php

class iceI18nPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    $this->dispatcher->connect('context.load_factories', array($this, 'defineConfiguration'));
  }

  public function defineConfiguration(sfEvent $event)
  {
    $context = $event->getSubject();
    
    if (!sfConfig::get('sf_i18n') || !$context->getI18N() instanceof iceI18n)
    {
      sfConfig::set('ice_i18n_enabled', false);
      
      return;
    }
    
    $i18n_options = $context->getI18N()->getOptions();

    sfConfig::set('ice_i18n_enabled', $context->getUser()->can(IceSecurityUser::CAN_TRANSLATE));
    sfConfig::set('ice_i18n_global_application', isset($i18n_options['global_application']) ? $i18n_options['global_application'] : $context->getConfiguration()->getApplication());
  }
}
