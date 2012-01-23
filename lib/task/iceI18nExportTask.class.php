<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2008 Qarmaq 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 *
 *
 * @package    iceI18nPlugin
 * @author     Thomas Rabaix <thomas.rabaix@soleoweb.com>
 * @version    SVN: $Id$
 */
class iceI18nExportTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    
    $this->addArguments(array(
      new sfCommandArgument('application', sfCommandArgument::REQUIRED, 'The application name'),
      new sfCommandArgument('source', sfCommandArgument::REQUIRED, 'The xliff file location of the remote'),
    ));
    
    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
    ));
    
    $this->aliases = array('ice-i18n-xliff-export');
    $this->namespace = 'i18n';
    $this->name = 'ice-xliff-export';
    $this->briefDescription = '[iceI18nPlugin] Export a database i18n_catalogue into a xliff file';

    $this->detailedDescription = <<<EOF
Export a database i18n_catalogue into a xliff file
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {

    $databaseManager = new sfDatabaseManager($this->configuration);
    
    // TODO
    
    throw new sfException('not implemented yet');
  }
}