<?php
/*
 * This file is part of the mgWidgetsPlugin package.
 * (c) 2008 MenuGourmet 
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
class iceI18NDatagrid extends swDoctrineDatagrid
{

  public function getModelName()
  {
    return "iceI18nTransUnit";
  }

  public function setupDatagrid()
  {

    $this->addFilter(
      'cat_id',
      null,
      new sfWidgetFormDoctrineSelect(array('model' => 'iceI18nCatalogue', 'add_empty' => true)),
      new sfValidatorDoctrineChoice(array('model' => 'iceI18nCatalogue', 'column' => 'cat_id', 'required' => false))
    );

    $this->addFilter(
      'source',
      null,
      new sfWidgetFormInput(),
      new sfValidatorString(array('required' => false))
    );
    
    $this->addFilter(
      'target',
      null,
      new sfWidgetFormInput(),
      new sfValidatorString(array('required' => false))
    );
    
   $this->addFilter(
      'translated',
      null,
      new sfWidgetFormSelect(array('choices' => array('-1' => '', 1 =>'yes', 0 =>'no'))),
      new sfValidatorNumber(array('required' => false))
    );
  }

  function buildQuery(Doctrine_Query $query) {

    $query->leftJoin('iceI18nTransUnit.iceI18nCatalogue c');

    if($this->getValue('cat_id'))
    {
      $query->addWhere('cat_id = ?', $this->getValue('cat_id'));
    }
    
    if(strlen($this->getValue('source')) > 0)
    {
      $query->addWhere('source LIKE ?', '%'.$this->getValue('source').'%');
    }
    
    if(strlen($this->getValue('target')) > 0)
    {
       $query->addWhere('target LIKE ?', '%'.$this->getValue('target').'%');
    }
    
    if(is_numeric($this->getValue('translated')) && $this->getValue('translated') >= 0)
    {
       $query->addWhere('translated = ?', $this->getValue('translated'));
    }
    
    return $query;
  }
}