<?php

class iceI18N extends sfI18N
{
  public function initialize(sfApplicationConfiguration $configuration, sfCache $cache = null, $options = array())
  {
    parent::initialize($configuration, $cache, $options);

    $this->options['learning_mode']  = isset($this->options['learning_mode']) ? $this->options['learning_mode'] : false;

    $this->configuration->loadHelpers(array('Text'));
  }

  public function __destruct()
  {
    if ($this->options['learning_mode'])
    {
      // Save only on learning mode
      if (!$this->getMessageSource() instanceof sfMessageSource_iceMySQL)
      {
        throw new sfException('The message source must be an instance of sfMessageSource_iceMySQL');
      }

      $this->getMessageSource()->save();
    }
  }

  /**
   * Gets the translation for the given string
   *
   * @param  string $string     The string to translate
   * @param  array  $args       An array of arguments for the translation
   * @param  string $catalogue  The catalogue name
   *
   * @return string The translated string
   */
  public function __($string, $args = array(), $catalogue = 'messages')
  {
    $catalogue = empty($catalogue) ? 'messages' : $catalogue;

    // get the translated message
    // if the debug is on then the message will be prefixed and suffixed
    $message = $this->getMessageFormat()->format($string, $args, $catalogue);

    $catalogue = sprintf('%s.%s', $this->getMessageSource()->getApplicationName(), $catalogue );

    if (!sfConfig::get('ice_i18n_enabled', false))
    {
      return $message;
    }

    $pseudo_string = $string;
    if ($this->options['debug'])
    {
      $pseudo_string = $this->options['untranslated_prefix'].$string.$this->options['untranslated_suffix'];
    }

    $args = empty($args) ? array() : $args;

    // Code is from I18nHelper.php file, replace object with strings
    foreach ($args as $key => $value)
    {
      if (is_object($value) && method_exists($value, '__toString'))
      {
        $args[$key] = $value->__toString();
      }
    }

    $pseudo_string = strtr($pseudo_string, $args);

    $value = array(
      'source'        => $string,
      'target'        => truncate_text($message, 70),
      'params'        => is_array($args) ? implode(', ', array_keys($args)) : '',
      'is_translated' => $pseudo_string != $message
    );

    // append the message, so it can be stored into the database
    $this->getMessageSource()->appendRequestedMessage($value, $catalogue);

    return $pseudo_string != $message ?  $message : $pseudo_string;
  }

  /**
   *
   * @return array messages requested in the current web request
   */
  public function getRequestedMessages()
  {
    return $this->getMessageSource()->getRequestedMessages();
  }

  /**
   * return the language used in a given catalogue name
   *
   * @static
   * @param  string $catalogue the catalogue name
   * @return string language
   */
  public static function getLanguage($catalogue)
  {
    return substr($catalogue, strrpos($catalogue, '.') + 1);
  }
}