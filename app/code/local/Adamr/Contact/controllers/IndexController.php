<?php
class Adamr_Contact_IndexController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
		$this->_redirect('*/list/all');
	}
}