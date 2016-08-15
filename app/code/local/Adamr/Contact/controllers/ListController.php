<?php
class Adamr_Contact_ListController extends Mage_Core_Controller_Front_Action
{
	public function allAction()
	{
		$this->loadLayout();

		$this->getLayout()->getBlock('head')->setTitle($this->__('Contact'));
		
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		
		$breadcrumbs->addCrumb('home', array(
				'label' => $this->__('Home Page'),
				'title' => $this->__('Home Page'),
				'link'  => Mage::getBaseUrl()
		 ));

		$breadcrumbs->addCrumb('contact', array(
				'label' => $this->__('Contact'),
				'title' => $this->__('Contact')
		));

		$this->renderLayout();
	}

	public function entryAction()
	{
		$this->loadLayout();   

		$this->getLayout()->getBlock('head')->setTitle($this->__('Contact'));

		$id = $this->getRequest()->getParam('id');

		if (!($id && ctype_digit($id)))
		{
			Mage::getSingleton('core/session')->addError($this->__('Entry does not exist.'));
			$this->_redirect('*/*/all');
		}

		Mage::register('current_entry', $id);
		
		$breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
		
		$breadcrumbs->addCrumb('home', array(
				'label' => $this->__('Home Page'),
				'title' => $this->__('Home Page'),
				'link'  => Mage::getBaseUrl()
		 ));

		$breadcrumbs->addCrumb('contact', array(
				'label' => $this->__('Contact'),
				'title' => $this->__('Contact'),
				'link'	=> Mage::getUrl('contact/list/all')
		));

		$breadcrumbs->addCrumb('entry', array(
				'label' => $this->__('Entry'),
				'title' => $this->__('Entry'),
		));

		$this->renderLayout();
	}

	public function addAction()
	{
		$response 	= [];
		$error 		= false;

		$name  		= $this->getRequest()->getParam('name');  
		$email 		= $this->getRequest()->getParam('email');

		if (!filter_var($name, FILTER_SANITIZE_STRING))
		{
			$response['errorMsg'] = $this->__('Name Error');
			$error = true;
		}

		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
		{
			$response['errorMsg'] = $this->__('Invalid Email');
			$error = true;
		}

		if (!$error)
		{
			$entry = Mage::getModel('contact/contact')->setData([
				'name' 		=> $name,
				'email' 	=> $email,
			])->save();

			$entryData 				= $entry->getData();
			$entryData['entry_url'] = Mage::getUrl('contact/list/entry', array('id' => $entryData['id']));

			if ($entry)
			{
				$response['error'] = 0;
				$response['data']  = $entryData;
			}

		} else {
			$response['error'] = 1;
		}

		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
	}

	public function deleteAction()
	{
		/* This should probably not be availabe without some autentication,
			but since this is not going to prodcution I let it be */
			
		$id = $this->getRequest()->getParam('id');

		if (!filter_var($id, FILTER_SANITIZE_NUMBER_INT))
		{
			$this->_redirect('*/*/all');
		}

		Mage::getModel('contact/contact')->delete((int) $id);

		$this->_redirect('*/*/all');
	}
}