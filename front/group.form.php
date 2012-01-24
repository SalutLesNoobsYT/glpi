<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2012 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

Session::checkRight("group", "r");

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$group     = new Group();
$groupuser = new Group_User();

if (isset($_POST["add"])) {
   $group->check(-1,'w',$_POST);
   if ($newID=$group->add($_POST)) {
      Event::log($newID, "groups", 4, "setup",
                 sprintf(__('%1$s adds the item %2%s'), $_SESSION["glpiname"], $_POST["name"]));
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   $group->check($_POST["id"],'w');
   $group->delete($_POST);
   Event::log($_POST["id"], "groups", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s purges the item'), $_SESSION["glpiname"]));
   $group->redirectToList();

} else if (isset($_POST["update"])) {
   $group->check($_POST["id"],'w');
   $group->update($_POST);
   Event::log($_POST["id"], "groups", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s updates the item'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["adduser"])) {
   $groupuser->check(-1,'w',$_POST);
   if ($groupuser->add($_POST)) {
      Event::log($_POST["groups_id"], "groups", 4, "setup",
                 //TRANS: %s is the user login
                 sprintf(__('%s adds a user to a group'), $_SESSION["glpiname"]));
   }
   Html::back();

} else if (isset($_POST['action']) && $_POST['action']=='deleteuser') {
   if (isset($_POST["item"]) && count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($groupuser->can($key,'w')) {
            $groupuser->delete(array('id' => $key));
         }
      }
   }
   Event::log($_POST["groups_id"], "groups", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s deletes users from a group'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST['action']) && $_POST['action']=='unset_manager') {
   if (isset($_POST["item"]) && count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($groupuser->can($key,'w')) {
            $groupuser->update(array('id'         => $key,
                                     'is_manager' => 0));
         }
      }
   }
   Event::log($_POST["groups_id"], "groups", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s unsets users as manager in a group'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST['action']) && $_POST['action']=='set_manager') {
   if (isset($_POST["item"]) && count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($groupuser->can($key,'w')) {
            $groupuser->update(array('id'         => $key,
                                     'is_manager' => 1));
         }
      }
   }
   Event::log($_POST["groups_id"], "groups", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s sets users as manager in a group'), $_SESSION["glpiname"]));

   Html::back();

} else if (isset($_POST['action']) && $_POST['action']=='unset_delegate') {
   if (isset($_POST["item"]) && count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($groupuser->can($key, 'w')) {
            $groupuser->update(array('id'              => $key,
                                     'is_userdelegate' => 0));
         }
      }
   }
   Event::log($_POST["groups_id"], "groups", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s unsets users as delegatee in a group'), $_SESSION["glpiname"]));

   Html::back();

} else if (isset($_POST['action']) && $_POST['action']=='set_delegate') {
   if (isset($_POST["item"]) && count($_POST["item"])) {
      foreach ($_POST["item"] as $key => $val) {
         if ($groupuser->can($key, 'w')) {
            $groupuser->update(array('id'              => $key,
                                     'is_userdelegate' => 1));
         }
      }
   }
   Event::log($_POST["groups_id"], "groups", 4, "setup",
              //TRANS: %s is the user login
              sprintf(__('%s sets users as delegatee in a group'), $_SESSION["glpiname"]));
   Html::back();

} else if (isset($_POST["changegroup"]) && isset($_POST["groups_id"]) && isset($_POST["field"])) {
   if (isset($_POST['item'])
       && ($_POST["field"]=='groups_id' || $_POST["field"]=='groups_id_tech' )) {
      foreach ($_POST['item'] as $type => $ids) {
         if ($item = getItemForItemtype($type)) {
            foreach ($ids as $id => $val) {
               if ($val && $item->can($id,'w')) {
                  $item->update(array('id'            => $id,
                                      $_POST["field"] => $_POST["groups_id"]));
               }
            }
         }
      }
   }
   Html::back();

} else {
   Html::header(Group::getTypeName(2), $_SERVER['PHP_SELF'], "admin", "group");
   $group->showForm($_GET["id"]);
   Html::footer();
}
?>