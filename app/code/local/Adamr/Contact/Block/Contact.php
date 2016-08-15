<?php   
class Adamr_Contact_Block_Contact extends Mage_Core_Block_Template
{
	protected $_contact;

	public function getCurrent()
	{
		$id = Mage::registry('current_entry');

		if ($id)
		{
			$entry = $this->getContact()->load($id);

			if ($entry)
			{
				return $entry->getData();
			}
		}
	}

	public function getContact()
	{
		if (null === $this->_contact)
		{
			$this->_contact = Mage::getModel('contact/contact');
		}

		return $this->_contact;
	}
}