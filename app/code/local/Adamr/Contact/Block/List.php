<?php   
class Adamr_Contact_Block_List extends Adamr_Contact_Block_Contact
{
	protected $_contact;

	public function getContactCollection()
	{
		return $this->getContact()
					->getCollection();
	}

	public function getEntryUrl($id)
	{
		return Mage::getUrl('contact/list/entry', array('id' => $id));
	}

	public function getColumns()
	{
		return $this->getContact()->getColumns();
	}
}