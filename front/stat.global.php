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
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

define('GLPI_ROOT', '..');
include (GLPI_ROOT . "/inc/includes.php");

Html::header(__('Statistics'), $_SERVER['PHP_SELF'], "maintain", "stat");

Session::checkRight("statistic", "1");


if (empty($_REQUEST["date1"]) && empty($_REQUEST["date2"])) {
   $year              = date("Y")-1;
   $_REQUEST["date1"] = date("Y-m-d", mktime(1,0,0,date("m"), date("d"), $year));
   $_REQUEST["date2"] = date("Y-m-d");
}

if (!empty($_REQUEST["date1"])
    && !empty($_REQUEST["date2"])
    && strcmp($_REQUEST["date2"],$_REQUEST["date1"]) < 0) {

   $tmp               = $_REQUEST["date1"];
   $_REQUEST["date1"] = $_REQUEST["date2"];
   $_REQUEST["date2"] = $tmp;
}

Stat::title();

$item = new $_REQUEST['itemtype']();

echo "<form method='get' name='form' action='stat.global.php'><div class='center'>";
echo "<table class='tab_cadre'>";
echo "<tr class='tab_bg_2'><td class='right'>".__('Start date')."</td><td>";
Html::showDateFormItem("date1", $_REQUEST["date1"]);
echo "</td><td rowspan='2' class='center'>";
echo "<input type='hidden' name='itemtype' value=\"".$_REQUEST['itemtype']."\">";

echo "<input type='submit' class='submit' value=\"".__s('Display report')."\"></td></tr>";

echo "<tr class='tab_bg_2'><td class='right'>".__('End date')."</td><td>";
Html::showDateFormItem("date2",$_REQUEST["date2"]);
echo "</td></tr>";
echo "</table></div>";

///////// Stats nombre intervention
// Total des interventions
$values['total']   = Stat::constructEntryValues($_REQUEST['itemtype'], "inter_total",
                                                $_REQUEST["date1"], $_REQUEST["date2"]);
// Total des interventions résolues
$values['solved']  = Stat::constructEntryValues($_REQUEST['itemtype'], "inter_solved",
                                                $_REQUEST["date1"], $_REQUEST["date2"]);
// Total des interventions closes
$values['closed']  = Stat::constructEntryValues($_REQUEST['itemtype'], "inter_closed",
                                                $_REQUEST["date1"], $_REQUEST["date2"]);
// Total des interventions closes
$values['late']    = Stat::constructEntryValues($_REQUEST['itemtype'], "inter_solved_late",
                                                $_REQUEST["date1"], $_REQUEST["date2"]);

$available = array('total'  => __('Opened'),
                   'solved' => __('Solved'),
                   'late'   => __('Late'),
                   'closed' => __('Closed'),);
echo "<div class='center'>";

$show_all = false;
if (!isset($_REQUEST['graph']) || count($_REQUEST['graph'])==0) {
   $show_all = true;
}


foreach ($available as $key => $name) {
   echo "<input type='checkbox' onchange='submit()' name='graph[$key]' ".
          ($show_all || isset($_REQUEST['graph'][$key])?"checked":"")."> ".$name."&nbsp;";
}
echo "</div>";

$toprint = array();
foreach ($available as $key => $name) {
   if ($show_all || isset($_REQUEST['graph'][$key])) {
      $toprint[$name] = $values[$key];
   }
}

Stat::showGraph($toprint, array('title'     => __('Number'),
                                'showtotal' => 1,
                                'unit'      => $item->getTypeName(2)));

//Temps moyen de resolution d'intervention
$values2['avgsolved'] = Stat::constructEntryValues($_REQUEST['itemtype'] ,"inter_avgsolvedtime",
                                                   $_REQUEST["date1"], $_REQUEST["date2"]);
// Pass to hour values
foreach ($values2['avgsolved'] as $key => $val) {
   $values2['avgsolved'][$key] /= HOUR_TIMESTAMP;
}

//Temps moyen de cloture d'intervention
$values2['avgclosed'] = Stat::constructEntryValues($_REQUEST['itemtype'], "inter_avgclosedtime",
                                                   $_REQUEST["date1"], $_REQUEST["date2"]);
// Pass to hour values
foreach ($values2['avgclosed'] as $key => $val) {
   $values2['avgclosed'][$key] /= HOUR_TIMESTAMP;
}
//Temps moyen d'intervention reel
$values2['avgactiontime'] = Stat::constructEntryValues($_REQUEST['itemtype'], "inter_avgactiontime",
                                                       $_REQUEST["date1"], $_REQUEST["date2"]);

// Pass to hour values
foreach ($values2['avgactiontime'] as $key => $val) {
   $values2['avgactiontime'][$key] /= HOUR_TIMESTAMP;
}


$available = array('avgclosed'      => __('Closure'),
                   'avgsolved'      => __('Resolution'),
                   'avgactiontime'  => __('Real duration'));


if ($_REQUEST['itemtype']=='Ticket') {
   $available['avgtaketime'] = __('Take into account');

   //Temps moyen de prise en compte de l'intervention
   $values2['avgtaketime'] = Stat::constructEntryValues($_REQUEST['itemtype'],
                                                        "inter_avgtakeaccount", $_REQUEST["date1"],
                                                        $_REQUEST["date2"]);

   // Pass to hour values
   foreach ($values2['avgtaketime'] as $key => $val) {
      $values2['avgtaketime'][$key] /= HOUR_TIMESTAMP;
   }
}


echo "<div class='center'>";

$show_all2 = false;
if (!isset($_REQUEST['graph2']) || count($_REQUEST['graph2'])==0) {
   $show_all2 = true;
}


foreach ($available as $key => $name) {
   echo "<input type='checkbox' onchange='submit()' name='graph2[$key]' ".
          ($show_all2||isset($_REQUEST['graph2'][$key])?"checked":"")."> ".
         $name."&nbsp;";
}
echo "</div>";

$toprint = array();
foreach ($available as $key => $name) {
   if ($show_all2 || isset($_REQUEST['graph2'][$key])) {
      $toprint[$name] = $values2[$key];
   }
}

Stat::showGraph($toprint, array('title'     => __('Average time'),
                                'unit'      => Toolbox::ucfirst(__('hour(s)')),
                                'showtotal' => 1,
                                'datatype'  => 'average'));


if ($_REQUEST['itemtype'] == 'Ticket') {

   ///////// Satisfaction
   $values['opensatisfaction']   = Stat::constructEntryValues($_REQUEST['itemtype'],
                                                              "inter_opensatisfaction",
                                                              $_REQUEST["date1"],
                                                              $_REQUEST["date2"]);

   $values['answersatisfaction'] = Stat::constructEntryValues($_REQUEST['itemtype'],
                                                              "inter_answersatisfaction",
                                                              $_REQUEST["date1"],
                                                              $_REQUEST["date2"]);


   $available = array('opensatisfaction'   => __('Opened'),
                      'answersatisfaction' => __('Answered'));
   echo "<div class='center'>";

   foreach ($available as $key => $name) {
      echo "<input type='checkbox' onchange='submit()' name='graph[$key]' ".
            ($show_all || isset($_REQUEST['graph'][$key])?"checked":"")."> ".$name."&nbsp;";
   }
   echo "</div>";

   $toprint = array();
   foreach ($available as $key => $name) {
      if ($show_all || isset($_REQUEST['graph'][$key])) {
         $toprint[$name] = $values[$key];
      }
   }

   Stat::showGraph($toprint, array('title'     => __('Satisfaction survey'),
                                   'showtotal' => 1,
                                   'unit'      => __('Ticket(s)')));


   $values['avgsatisfaction'] = Stat::constructEntryValues($_REQUEST['itemtype'],
                                                           "inter_avgsatisfaction",
                                                           $_REQUEST["date1"], $_REQUEST["date2"]);

   $available = array('avgsatisfaction' => __('Satisfaction'));
   echo "<div class='center'>";

   foreach ($available as $key => $name) {
      echo "<input type='checkbox' onchange='submit()' name='graph[$key]' ".
            ($show_all||isset($_REQUEST['graph'][$key])?"checked":"")."> ".$name."&nbsp;";
   }
   echo "</div>";

   $toprint = array();
   foreach ($available as $key => $name) {
      if ($show_all || isset($_REQUEST['graph'][$key])) {
         $toprint[$name] = $values[$key];
      }
   }

   Stat::showGraph($toprint, array('title' => __('Satisfaction')));
}

echo "</form>";
Html::footer();
?>