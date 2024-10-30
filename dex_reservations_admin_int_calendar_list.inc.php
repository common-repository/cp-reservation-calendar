<?php

if ( !is_admin() ) 
{
    echo 'Direct access not allowed.';
    exit;
}


$current_user = wp_get_current_user();

global $wpdb;
$message = "";
if (isset($_GET['a']) && $_GET['a'] == '1' && wp_verify_nonce( $_REQUEST['r'], 'dexres_update_list'))
{
    $sql .= 'INSERT INTO `'.DEX_RESERVATIONS_CONFIG_TABLE_NAME.'` (`'.TDE_RESERVATIONCONFIG_TITLE.'`,`'.TDE_RESERVATIONCONFIG_USER.'`,`'.TDE_RESERVATIONCONFIG_PASS.'`,`'.TDE_RESERVATIONCONFIG_LANG.'`,`'.TDE_RESERVATIONCONFIG_CPAGES.'`,`'.TDE_RESERVATIONCONFIG_MSG.'`,`'.TDE_RESERVATIONCALDELETED_FIELD.'`,calendar_mode) '.
            ' VALUES("","'.esc_sql(sanitize_text_field($_GET["name"])).'","","ENG","1","Please, select your reservation.","0","true");';

    $wpdb->query($sql);   

    $results = $wpdb->get_results('SELECT `'.TDE_RESERVATIONCONFIG_ID.'` FROM `'.DEX_RESERVATIONS_CONFIG_TABLE_NAME.'` ORDER BY `'.TDE_RESERVATIONCONFIG_ID.'` DESC LIMIT 0,1');        
    $wpdb->query('UPDATE `'.DEX_RESERVATIONS_CONFIG_TABLE_NAME.'` SET `'.TDE_RESERVATIONCONFIG_TITLE.'`="cal'.intval($results[0]->id).'" WHERE `'.TDE_RESERVATIONCONFIG_ID.'`='.intval($results[0]->id));           
    $message = "Item added";
} 
else if (isset($_GET['u']) && $_GET['u'] != '' && wp_verify_nonce( $_REQUEST['r'], 'dexres_update_list'))
{
    $wpdb->query('UPDATE `'.DEX_RESERVATIONS_CONFIG_TABLE_NAME.'` SET conwer='.intval($_GET["owner"]).',`'.TDE_RESERVATIONCALDELETED_FIELD.'`='.intval($_GET["public"]).',`'.TDE_RESERVATIONCONFIG_USER.'`="'.esc_sql(sanitize_text_field($_GET["name"])).'" WHERE `'.TDE_RESERVATIONCONFIG_ID.'`='.intval($_GET['u']));           
    $message = "Item updated";        
}
else if (isset($_GET['d']) && $_GET['d'] != '' && wp_verify_nonce( $_REQUEST['r'], 'dexres_update_list'))
{
    $wpdb->query('DELETE FROM `'.DEX_RESERVATIONS_CONFIG_TABLE_NAME.'` WHERE `'.TDE_RESERVATIONCONFIG_ID.'`='.intval($_GET['d']));       
    $message = "Item deleted";
}


$nonce = wp_create_nonce( 'dexres_update_list' );

if ($message) echo "<div id='setting-error-settings_updated' class='updated settings-error'><p><strong>".$message."</strong></p></div>";

?>
<div class="wrap">
<h1>CP Reservation Calendar</h1>




<script type="text/javascript">
 function cp_addItem()
 {
    var calname = document.getElementById("cp_itemname").value;
    document.location = 'admin.php?page=dex_reservations&a=1&r=<?php echo $nonce?>&name='+encodeURIComponent(calname);       
 }
 
 function cp_updateItem(id)
 {
    var calname = document.getElementById("calname_"+id).value;
    var owner = document.getElementById("calowner_"+id).options[document.getElementById("calowner_"+id).options.selectedIndex].value;    
    if (owner == '')
        owner = 0;
    var is_public = (document.getElementById("calpublic_"+id).checked?"0":"1");
    document.location = 'admin.php?page=dex_reservations&u='+id+'&r=<?php echo $nonce?>&public='+is_public+'&owner='+owner+'&name='+encodeURIComponent(calname);    
 }
 
 function cp_manageSettings(id)
 {
    document.location = 'admin.php?page=dex_reservations&cal='+id+'&r=<?php echo $nonce?>';
 }
 
 function cp_BookingsList(id)
 {
    document.location = 'admin.php?page=dex_reservations&cal='+id+'&list=1&r=<?php echo $nonce?>';
 }
 
 function cp_deleteItem(id)
 {
    if (confirm('Are you sure that you want to delete this item?'))
    {        
        document.location = 'admin.php?page=dex_reservations&d='+id+'&r=<?php echo $nonce?>';
    }
 }
 
</script>


<div id="normal-sortables" class="meta-box-sortables">


 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Calendar List / Items List</span></h3>
  <div class="inside">
  
  
  <table cellspacing="1"> 
   <tr>
    <th align="left">ID</th><th align="left">Calendar Name</th><th align="left">Owner</th><th align="left">Public</th><th align="left">&nbsp; &nbsp; Options</th><th align="left">Shortcode for pages/posts</th>
   </tr> 
<?php  

  $users = $wpdb->get_results( "SELECT user_login,ID FROM ".$wpdb->users." ORDER BY ID DESC" );                                                                     

  $myrows = $wpdb->get_results( "SELECT * FROM ".DEX_RESERVATIONS_CONFIG_TABLE_NAME );                                                                     
  foreach ($myrows as $item)   
      if (cp_reservation_is_administrator() || ($current_user->ID == $item->conwer))
      {
?>
   <tr> 
    <td nowrap><?php echo $item->id; ?></td>
    <td nowrap><input type="text" style="width:100px;" <?php if (!cp_reservation_is_administrator()) echo ' readonly '; ?>name="calname_<?php echo $item->id; ?>" id="calname_<?php echo $item->id; ?>" value="<?php echo esc_attr($item->uname); ?>" /></td>
    
    <?php if (cp_reservation_is_administrator()) { ?>
    <td nowrap>
      <select name="calowner_<?php echo $item->id; ?>" id="calowner_<?php echo $item->id; ?>">
       <option value="0"<?php if (!$item->conwer) echo ' selected'; ?>></option>
       <?php foreach ($users as $user) { 
       ?>
          <option value="<?php echo $user->ID; ?>"<?php if ($user->ID."" == $item->conwer) echo ' selected'; ?>><?php echo $user->user_login; ?></option>
       <?php  } ?>
      </select>
    </td>    
    <?php } else { ?>
        <td nowrap>
        <?php echo $current_user->user_login; ?>
        </td>
    <?php } ?>
    
    <td nowrap align="center">
       <?php if (cp_reservation_is_administrator()) { ?> 
         &nbsp; &nbsp; <input type="checkbox" name="calpublic_<?php echo $item->id; ?>" id="calpublic_<?php echo $item->id; ?>" value="1" <?php if (!$item->caldeleted) echo " checked "; ?> />
       <?php } else { ?>  
         <?php if (!$item->caldeleted) echo "Yes"; else echo "No"; ?> 
       <?php } ?>   
    </td>    
    <td nowrap>&nbsp; &nbsp; 
                             <?php if (cp_reservation_is_administrator()) { ?> 
                               <input type="button" name="calupdate_<?php echo $item->id; ?>" value="Update" onclick="cp_updateItem(<?php echo $item->id; ?>);" /> &nbsp; 
                             <?php } ?>    
                             <input type="button" name="calmanage_<?php echo $item->id; ?>" value="Settings " onclick="cp_manageSettings(<?php echo $item->id; ?>);" /> &nbsp;                              
                          
    </td>
    <td>[CP_RESERVATION_CALENDAR]</td>
   </tr>
<?php  
      } 
?>   
     
  </table> 
    
<br /><br />
<div style="border:1px solid #664444;background-color:#FDFDC8;width:90%;padding-left:10px;padding-right:10px;font-size:12px;">
    <p style="font-size:15px;"><strong>Important note:</strong><br />
        <br /> We have developed a more advanced plugin based in this one that contains <strong>all the features present in this plugin plus a large additional set of features</strong>.</p>

     <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>We recommend to move to the following plugin:</span></h3>
  <div class="inside"> 
   
    <div class="plugin-card plugin-card-booking-calendar-contact-form">
       <div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="plugin-install.php?tab=plugin-information&amp;plugin=booking-calendar-contact-form&amp;" class="thickbox open-plugin-details-modal">
						Booking Calendar Contact Form						<img src="https://ps.w.org/booking-calendar-contact-form/assets/icon-256x256.png" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons"><ul class="plugin-action-buttons">
                    <?php if (!is_plugin_active('appointment-hour-booking/app-booking-plugin.php')) { ?>
                      <li><a class="install-now button" data-slug="booking-calendar-contact-form" href="<?php echo wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=booking-calendar-contact-form'), 'install-plugin_booking-calendar-contact-form'); ?>" aria-label="Install  Booking Calendar Contact Form" data-name="Booking Calendar Contact Form">Install Now</a></li>
                    <?php } else { ?>
                      <li><a class="install-now button" data-slug="booking-calendar-contact-form" href="" aria-label="Install  Booking Calendar Contact Form" data-name="Booking Calendar Contact Form">Settings</a></li>
                    <?php } ?>
                    <li><a href="plugin-install.php?tab=plugin-information&amp;plugin=booking-calendar-contact-form" class="thickbox open-plugin-details-modal" aria-label="More information about Booking Calendar Contact Form" data-title="Booking Calendar Contact Form">More Details</a></li></ul>				</div>
				<div class="desc column-description">
					<p>Booking form for booking range of dates (selecting start date and end date). Example  hotel booking, car rental, room booking, etc... PayPal integration included.</p>					
				</div>
			</div>
    </div>  
    
    <div style="clear:both"></div>        

  </div>    
 </div>
 
 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Other related reservation calendar plugins:</span></h3>
  <div class="inside"> 

    <div class="plugin-card plugin-card-appointment-hour-booking">
       <div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="plugin-install.php?tab=plugin-information&amp;plugin=appointment-hour-booking&amp;" class="thickbox open-plugin-details-modal">
						Appointment Hour Booking						<img src="https://ps.w.org/appointment-hour-booking/assets/icon-256x256.png" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons"><ul class="plugin-action-buttons">
                    <?php if (!is_plugin_active('appointment-hour-booking/app-booking-plugin.php')) { ?>
                      <li><a class="install-now button" data-slug="appointment-hour-booking" href="<?php echo wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=appointment-hour-booking'), 'install-plugin_appointment-hour-booking'); ?>" aria-label="Install Appointment Hour Booking" data-name="Appointment Hour Booking">Install Now</a></li>
                    <?php } else { ?>
                      <li><a class="install-now button" data-slug="appointment-hour-booking" href="admin.php?page=cp_apphourbooking" aria-label=" Appointment Hour Booking" data-name="Appointment Hour Booking">Settings</a></li>
                    <?php } ?>
                    <li><a href="plugin-install.php?tab=plugin-information&amp;plugin=appointment-hour-booking" class="thickbox open-plugin-details-modal" aria-label="More information about Appointment Hour Booking" data-title="Appointment Hour Booking">More Details</a></li></ul>				</div>
				<div class="desc column-description">
					<p>Booking forms for appointments/services with a start time and a defined duration over a schedule. The start time is visually selected by the end user from a set of start times (based in "open" hours and service duration).</p>
					
				</div>
			</div>
    </div>  
    
   <div class="plugin-card plugin-card-appointment-booking-calendar">
       <div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="plugin-install.php?tab=plugin-information&amp;plugin=appointment-booking-calendar&amp;" class="thickbox open-plugin-details-modal">
						Appointment Booking Calendar						<img src="https://ps.w.org/appointment-booking-calendar/assets/icon-256x256.png" class="plugin-icon" alt="">
						</a>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons"><ul class="plugin-action-buttons">
                    <?php if (!is_plugin_active('appointment-booking-calendar/cpabc_appointments.php')) { ?>
                     <li><a class="install-now button" data-slug="appointment-booking-calendar" href="<?php echo wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=appointment-booking-calendar'), 'install-plugin_appointment-booking-calendar'); ?>" aria-label="Install  Appointment Booking Calendar" data-name="Appointment Booking Calendar">Install Now</a></li>
                    <?php } else { ?>
                     <li><a class="install-now button" data-slug="appointment-booking-calendar" href="" aria-label="  Appointment Booking Calendar" data-name="Appointment Booking Calendar">Settings</a></li>
                    <?php } ?>
                    <li><a href="plugin-install.php?tab=plugin-information&amp;plugin=appointment-booking-calendar" class="thickbox open-plugin-details-modal" aria-label="More information about Appointment Booking Calendar" data-title="Appointment Booking Calendar">More Details</a></li></ul>				</div>
				<div class="desc column-description">
					<p>Appointment booking calendar for booking time-slots into dates from a set of available time-slots in a calendar. Includes PayPal payments integration for processing the bookings.</p>
					
				</div>
			</div>
    </div>  
    
    <div style="clear:both"></div>  
    
  </div>  
</div>    
 
</div>    
    
   
  </div>    
 </div> 
 
  
</div> 


[<a href="https://wordpress.dwbooster.com/contact-us" target="_blank">Request Custom Modifications</a>] | [<a href="https://wordpress.dwbooster.com/calendars/cp-reservation-calendar" target="_blank">Help</a>]
</form>
</div>














