<?php
require_once 'Services/User/test/ilObjUserTest.php';

/**
 *
 */
class fürJonatan extends ilObjUserTest
{
  public function testCreateSetLookupDelete()
  	{
  		include_once("./Services/User/classes/class.ilObjUser.php");


  		// delete all aatestuser from previous runs
  		while (($i = ilObjUser::_lookupId("aatestuser")) > 0)
  		{
  			$user = new ilObjUser($i);
  			$user->delete();
  		}

  		$user = new ilObjUser();

  		// creation
  		$d = array(
  			"login" => "aatestuser",
  			"passwd_type" => IL_PASSWD_PLAIN,
  			"passwd" => "password",
  			"gender" => "m",
  			"firstname" => "Max",
  			"lastname" => "Mutzke",
  			"email" => "de@de.de",
  			"client_ip" => "1.2.3.4",
  			"ext_account" => "ext_mutzke"
  		);
  		$user->assignData($d);
  		$user->create();
  		$user->saveAsNew();
  		$user->setLanguage("no");
  		$user->writePrefs();
  		$id = $user->getId();
  		$value.= $user->getFirstname()."-";

  		// update
  		$user->setFirstname("Maxi");
  		$user->update();
  		$value.= $user->getFirstname()."-";

  		// other update methods
  		$user->refreshLogin();

  		// lookups
  		$value.= ilObjUser::_lookupEmail($id)."-";
  		$value.= ilObjUser::_lookupGender($id)."-";
  		$value.= ilObjUser::_lookupClientIP($id)."-";
  		$n = ilObjUser::_lookupName($id);
  		$value.= $n["lastname"]."-";
  		ilObjUser::_lookupFields($id);
  		$value.= ilObjUser::_lookupLogin($id)."-";
  		$value.= ilObjUser::_lookupExternalAccount($id)."-";
  		$value.= ilObjUser::_lookupId("aatestuser")."-";
  		ilObjUser::_lookupLastLogin($id);
  		$value.= ilObjUser::_lookupLanguage($id)."-";
  		ilObjUser::_readUsersProfileData(array($id));
  		if (ilObjUser::_loginExists("aatestuser"))
  		{
  			$value.= "le-";
  		}

  		// preferences...
  		$user->writePref("testpref", "pref1");
  		$value.= ilObjUser::_lookupPref($id, "testpref")."-";
  		$user->deletePref("testpref");
  		if (ilObjUser::_lookupPref($id, "testpref") == "")
  		{
  			$value.= "pref2"."-";
  		}

  		// activation
  		$user->setActive(false);
  		if (!ilObjUser::getStoredActive($id));
  		{
  			$value.= "act1-";
  		}
  		$user->setActive(true);
  		if (ilObjUser::getStoredActive($id));
  		{
  			$value.= "act2-";
  		}
  		ilObjUser::_toggleActiveStatusOfUsers(array($id), false);
  		if (!ilObjUser::getStoredActive($id));
  		{
  			$value.= "act3-";
  		}

  		// deletion
  	   //	$user->delete();

  		$this->assertEquals("Max-Maxi-de@de.de-m-1.2.3.4-Mutzke-aatestuser-ext_mutzke-$id-no-le-".
  			"pref1-pref2-act1-act2-act3-",
  			$value);
  	}




}

class test {

  public function jo(){
  $Obj = new fürJonatan ();
  $Obj-> testCreateSetLookupDelete();
  }
}
?>
