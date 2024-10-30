<?php

	$nzshpcrt_gateways[$num] = array(
		'name' => 'LDW WP e-Commerce iDeal',
		'api_version' => 2.0,
		'class_name' => 'wpsc_merchant_LDB_iDEAL',
		'has_recurring_billing' => true,
		'display_name' => 'iDeal',	
		'wp_admin_cannot_cancel' => false,
		'requirements' => array(),
		'form' => 'form_LDB_ideal',
		'submit_function' => 'submit_LDB_ideal',
		'internalname' => 'LDB_ideal',
	);

	class wpsc_merchant_LDB_iDEAL extends wpsc_merchant {
		function LDB_appendHash($hashString)
		{
			/*
				Create a proper iDEAL verification hash
			*/
			$hashString = str_replace(array(" - ", " ", "\t", "\n", "&amp;", "&lt;", "&gt;", "&quote;"), array("", "", "", "", "&", "<", ">", "\""), $hashString);
		    $hash = sha1($hashString);
		    return $hash;
		}
	
		function submit()
		{
			if (get_option('ldb_ideal_type') == 'normal' || get_option('ldb_ideal_type') == ''){
				$this->submitnormal();
			} else {
				$this->submitpspid();
			}
		}

		function submitpspid() {
			global $wpdb;
			$this->set_purchase_processed_by_purchid(2);
			$purchase_log_sql = 'SELECT * FROM ' . WPSC_TABLE_PURCHASE_LOGS . ' WHERE sessionid= "' . $this->cart_data['session_id'] . '" LIMIT 1';
			$purchase_log = $wpdb->get_results($purchase_log_sql,ARRAY_A);
			$amount = nzshpcrt_overall_total_price($_SESSION['delivery_country']);
			$phpamount = intval(round($amount*100));

			$idealname = '';
			$idealemail = '';
			$idealzip = '';
			$idealaddress = '';
			$idealcountry = '';
			$idealcity = '';
			$idealphone = '';

			if($_POST['collected_data'][get_option('ldb_ideal_pspid_first_name')] != ''){
				$idealname = $_POST['collected_data'][get_option('ldb_ideal_pspid_first_name')] . ' ' . $_POST['collected_data'][get_option('ldb_ideal_pspid_last_name')];
			}
			
			if($_POST['collected_data'][get_option('ldb_ideal_pspid_email')] != ''){
				$idealemail = $_POST['collected_data'][get_option('ldb_ideal_pspid_email')];
			}
			
			if($_POST['collected_data'][get_option('ldb_ideal_pspid_zip')] != ''){
				$idealzip = $_POST['collected_data'][get_option('ldb_ideal_pspid_zip')];
			}
			
			if($_POST['collected_data'][get_option('ldb_ideal_pspid_address')] != ''){
				$idealaddress = $_POST['collected_data'][get_option('ldb_ideal_pspid_address')];
			}
			
			if($_POST['collected_data'][get_option('ldb_ideal_pspid_country')] != ''){
				$idealcountry = $_POST['collected_data'][get_option('ldb_ideal_pspid_country')][0];
			}
			
			if($_POST['collected_data'][get_option('ldb_ideal_pspid_city')] != ''){
				$idealcity = $_POST['collected_data'][get_option('ldb_ideal_pspid_city')];
			}
			
			if($_POST['collected_data'][get_option('ldb_ideal_pspid_country')] != ''){
				$idealphone = $_POST['collected_data'][get_option('ldb_ideal_pspid_phone')];
			}
			$accepturl = $this->cart_data['transaction_results_url'];
			if( strpos( $accepturl, '?' ) === false ) {
				$accepturl.= '?sessionid=' . $this->cart_data['session_id'];
			} else {
				$accepturl.= '&sessionid=' . $this->cart_data['session_id'];
			}

?>
			<body>
				<form method="post" action="<?php echo get_option('ldb_ideal_pspid_url'); ?>" id="ideal_form" name="ideal_form">
					<input type="hidden" NAME="PSPID" value="<?php echo get_option('ldb_ideal_pspid_id'); ?>" />
					<input type="hidden" NAME="AMOUNT" value="<?php echo $phpamount; ?>" />
					<input type="hidden" NAME="ORDERID" value="<?php echo $purchase_log[0]['id'];?>" />
					<input type="hidden" name="CURRENCY" value="<?php echo get_option('ldb_ideal_currency'); ?>" />
					<input type="hidden" name="LANGUAGE" value="<?php echo get_option('ldb_ideal_language'); ?>" />
					<input type="hidden" name="ACCEPTURL" value="<?php echo $accepturl; ?>">
					<input type="hidden" name="CANCELURL" value="<?php echo get_option('shopping_cart_url'); ?>">
					<input type="hidden" name="DECLINEURL" value="<?php echo get_option('shopping_cart_url'); ?>">
					<input type="hidden" name="EXCEPTIONURL" value="<?php echo get_option('shopping_cart_url'); ?>">
					<!--customer information starts-->
					<input type="hidden" name="CN" value="<?php echo $idealname; ?>">
					<input type="hidden" name="EMAIL" value="<?php echo $idealemail; ?>">
					<input type="hidden" name="OWNERZIP" value="<?php echo $idealzip; ?>">
					<input type="hidden" name="OWNERADDRESS" value="<?php echo $idealaddress; ?>">
					<input type="hidden" name="OWNERCTY" value="<?php echo $idealcountry; ?>">
					<input type="hidden" name="OWNERTOWN" value="<?php echo $idealcity; ?>">
					<input type="hidden" name="OWNERTELNO" value="<?php echo $idealphone; ?>">
					<input type="hidden" name="COM" value="Order <?php echo $purchase_log[0]['id']; ?>">
					<!--customer information ends-->
					<input type="hidden" name="PM" value="iDEAL" />
				</form>
				<script type="text/javascript">
					document.ideal_form.submit();
				</script>
			</body>
<?php
			die();
			exit();
		}

		function submitnormal() {
			/*
				Set up the form which transfers the data to iDEAL.
			*/
			global $wpdb;
			$this->set_purchase_processed_by_purchid(2);
			$purchase_log_sql = 'SELECT * FROM ' . WPSC_TABLE_PURCHASE_LOGS . ' WHERE sessionid=' . $this->cart_data['session_id'] . ' LIMIT 1';
			$purchase_log = $wpdb->get_results($purchase_log_sql, ARRAY_A);
			$amount = nzshpcrt_overall_total_price($_SESSION['delivery_country']);
			$validUntil = date('Y-m-d\TH:i:s.000\Z', strtotime ('+1 week'));
			$phpamount = intval(round($amount*100));
			$paymenttype = 'ideal';
			$subid = 0;
			$tempsub = get_option('ldb_ideal_subid');
			if( !empty( $tempsub ) ) {
				$subid = $tempsub;
			}
			$hash = $this->LDB_appendHash(get_option('ldb_ideal_key') . ' - ' . get_option('ldb_ideal_id') . ' - ' . $subid . ' - ' . $phpamount . ' - ' . $purchase_log[0]['id'] . ' - ' . $paymenttype . ' - ' . $validUntil . ' - ' . '1' . ' - ' . 'Total' . ' - ' . '1'. ' - ' . $phpamount);
?>
			<body>
				<form method="post" action="<?php echo get_option('ldb_ideal_url');?>" id="ideal_form" name="ideal_form">
					<input type="hidden" name="merchantID" value="<?php echo get_option('ldb_ideal_id');?>" />
					<input type="hidden" name="subID" value="<?php echo $subid; ?>">
					<input type="hidden" name="purchaseID" value="<?php echo $purchase_log[0]['id'];?>" />
					<input type="hidden" name="description" value="Order <?php echo $purchase_log[0]['id'];?>" />
					<input type="hidden" name="amount" value="<?php echo $phpamount; ?>" />
					<input type="hidden" name="validUntil" value="<?php echo $validUntil; ?>" />
					<input type="hidden" name="currency" value="<?php echo get_option('ldb_ideal_currency');?>" />
					<input type="hidden" name="language" value="<?php echo get_option('ldb_ideal_language');?>" />
					<input type="hidden" name="urlSuccess" value="<?php echo get_option('ldb_ideal_urlSuccess');?>" />
					<input type="hidden" name="urlCancel" value="<?php echo get_option('ldb_ideal_urlCancel');?>" />
					<input type="hidden" name="urlError" value="<?php echo get_option('ldb_ideal_urlError');?>" />
					<input type="hidden" name="paymentType" value="<?php echo $paymenttype; ?>" />
					<input type="hidden" name="itemNumber1" value="1" />
					<input type="hidden" name="itemDescription1" value="Total" />
					<input type="hidden" name="itemQuantity1" value="1" />
					<input type="hidden" name="itemPrice1" value="<?php echo $phpamount; ?>" />
					<input type="hidden" name="hash" value="<?php echo $hash; ?>" />
				</form>
				<script type="text/javascript">
					document.ideal_form.submit();
				</script>
			</body>
<?php
			die();
			exit();
		}
	}
	
	function submit_LDB_ideal()
	{
		/*
			Save the posted variables
		*/
		if(isset($_POST['ldb_ideal_type'])) {
			update_option('ldb_ideal_type', $_POST['ldb_ideal_type']);
		}
		if(isset($_POST['ldb_ideal_id'])) {
			update_option('ldb_ideal_id', $_POST['ldb_ideal_id']);
		}
		if(isset($_POST['ldb_ideal_subid'])) {
			update_option('ldb_ideal_subid', $_POST['ldb_ideal_subid']);
		}
		if(isset($_POST['ldb_ideal_pspid_id'])) {
			update_option('ldb_ideal_pspid_id', $_POST['ldb_ideal_pspid_id']);
		}
		if(isset($_POST['ldb_ideal_urlSuccess'])) {
			update_option('ldb_ideal_urlSuccess', $_POST['ldb_ideal_urlSuccess']);
		}
		if(isset($_POST['ldb_ideal_urlCancel'])) {
			update_option('ldb_ideal_urlCancel', $_POST['ldb_ideal_urlCancel']);
		}
		if(isset($_POST['ldb_ideal_urlError'])) {
			update_option('ldb_ideal_urlError', $_POST['ldb_ideal_urlError']);
		}
		if(isset($_POST['ldb_ideal_url'])) {
			update_option('ldb_ideal_url', $_POST['ldb_ideal_url']);
		}
		if(isset($_POST['ldb_ideal_pspid_url'])) {
			update_option('ldb_ideal_pspid_url', $_POST['ldb_ideal_pspid_url']);
		}
		if(isset($_POST['ldb_ideal_key'])) {
			update_option('ldb_ideal_key', $_POST['ldb_ideal_key']);
		}
		if(isset($_POST['ldb_ideal_pspid_first_name'])) {
			update_option('ldb_ideal_pspid_first_name', $_POST['ldb_ideal_pspid_first_name']);
		}
		if(isset($_POST['ldb_ideal_pspid_last_name'])) {
			update_option('ldb_ideal_pspid_last_name', $_POST['ldb_ideal_pspid_last_name']);
		}
		if(isset($_POST['ldb_ideal_pspid_email'])) {
			update_option('ldb_ideal_pspid_email', $_POST['ldb_ideal_pspid_email']);
		}
		if(isset($_POST['ldb_ideal_pspid_address'])) {
			update_option('ldb_ideal_pspid_address', $_POST['ldb_ideal_pspid_address']);
		}
		if(isset($_POST['ldb_ideal_pspid_city'])) {
			update_option('ldb_ideal_pspid_city', $_POST['ldb_ideal_pspid_city']);
		}
		if(isset($_POST['ldb_ideal_pspid_phone'])) {
			update_option('ldb_ideal_pspid_phone', $_POST['ldb_ideal_pspid_phone']);
		}
		if(isset($_POST['ldb_ideal_pspid_zip'])) {
			update_option('ldb_ideal_pspid_zip', $_POST['ldb_ideal_pspid_zip']);
		}
		if(isset($_POST['ldb_ideal_pspid_country'])) {
			update_option('ldb_ideal_pspid_country', $_POST['ldb_ideal_pspid_country']);
		}
		if(isset($_POST['ldb_ideal_currency'])) {
			update_option('ldb_ideal_currency', $_POST['ldb_ideal_currency']);
		}
		if(isset($_POST['ldb_ideal_language'])) {
			update_option('ldb_ideal_language', $_POST['ldb_ideal_language']);
		}
		return true;
	}
	
	function form_LDB_ideal()
	{
		/*
			Set up the variables
		*/
		$language1 = '';
		$language2 = '';
		$language3 = '';
		$type1 = '';
		$type2 = '';
		$currency1 = '';
		$currency2 = '';
		$currency3 = '';
		$typetablenormal = 'display: none;';
		$typetablepspid = 'display: none;';

		/*
			Determine selected options
		*/
		if (get_option('ldb_ideal_language') == 'en_US'){
			$language1 = 'selected="selected" ';
		} else if (get_option('ldb_ideal_language') == 'nl_NL') {
			$language2 = 'selected="selected" ';
		} else if (get_option('ldb_ideal_language') == 'fr_FR') {
			$language3 = 'selected="selected" ';
		}

		if (get_option('ldb_ideal_type') == 'normal'){
			$type1 = 'selected="selected" ';
		} else if (get_option('ldb_ideal_type') == 'pspid') {
			$type2 = 'selected="selected" ';
		}

		if (get_option('ldb_ideal_type') == 'normal' || get_option('ldb_ideal_type') == ''){
			$typetablenormal = 'display: block;';
		} else {
			$typetablepspid = 'display: block;';
		}
		
		if (get_option('ldb_ideal_currency') == 'EUR'){
			$currency1 = 'selected="selected" ';
		} else if(get_option('ldb_ideal_currency') == 'USD') {
			$currency2 = 'selected="selected" ';
		} else if(get_option('ldb_ideal_currency') == 'GBP') {
			$currency3 = 'selected="selected" ';
		}
		
		$pspid_first_name = nzshpcrt_form_field_list( get_option( 'ldb_ideal_pspid_first_name' ) );
		$pspid_last_name = nzshpcrt_form_field_list( get_option( 'ldb_ideal_pspid_last_name' ) );
		$pspid_email = nzshpcrt_form_field_list( get_option( 'ldb_ideal_pspid_email' ) );
		$pspid_address = nzshpcrt_form_field_list( get_option( 'ldb_ideal_pspid_address' ) );
		$pspid_zip = nzshpcrt_form_field_list( get_option( 'ldb_ideal_pspid_zip' ) );
		$pspid_city = nzshpcrt_form_field_list( get_option( 'ldb_ideal_pspid_city' ) );
		$pspid_country = nzshpcrt_form_field_list( get_option( 'ldb_ideal_pspid_country' ) );
		$pspid_phone = nzshpcrt_form_field_list( get_option( 'ldb_ideal_pspid_phone' ) );
		
		/*
			Create the form
		*/
		$output = '
			<tr>
				<td style="width:150px;">
					iDeal type <a href="#" title="The type of iDeal connection you are using." style="text-decoration: none;">*</a>
				</td>
				<td>
					<select name="ldb_ideal_type" onchange="javascript:switchType(this.value);">
						<option ' . $type1 . 'value="normal">Normal</option>
						<option ' . $type2 . 'value="pspid">PSPID</option>
					</select>
				</td>
			</tr>
		</table>
		<script type="text/javascript">
			function switchType(type) {
				jQuery(".ldb_wp_ideal_table").hide();
				jQuery("#" + type).show();
			}
		</script>
		<style>
			.ldb_select {
				width: 198px;
			}
		</style>
		<table id="normal" cellspacing="0" cellpadding="0" class="form-table ldb_wp_ideal_table" style="' . $typetablenormal . '">
			<tbody>
				<tr>
					<td style="width:150px !important;">
						iDeal MerchantID <a href="#" title="Your iDeal ID number, also named AcceptantID." style="text-decoration: none;">*</a>
					</td>
					<td>
						<input type="text" size="20" value="' . get_option('ldb_ideal_id') . '" name="ldb_ideal_id" />
					</td>
				</tr>
				<tr>
					<td style="width:150px !important;">
						iDeal SubID <a href="#" title="Your iDeal SubID number, in most cases this should be \'0\'." style="text-decoration: none;">*</a>
					</td>
					<td>
						<input type="text" size="20" value="' . get_option('ldb_ideal_subid') . '" name="ldb_ideal_subid" />
					</td>
				</tr>
				<tr>
					<td>
						iDeal MerchantKey <a href="#" title="Also named Hash Key or \'Geheime sleutel\', generated and defined in \'Configuratie\' in your online iDeal Dashboard. (Hash Key for testing has to differ from the key used for production)" style="text-decoration: none;">*</a>
					</td>
					<td>
						<input type="text" size="20" value="' . get_option('ldb_ideal_key') . '" name="ldb_ideal_key" />
					</td>
				</tr>
				<tr>
					<td>
						iDeal URL <a href="#" title="The address of your account\'s iDEAL site. Most banks require a test sequence before going live which can be done through the test URLs." style="text-decoration: none;">*</a>
					</td>
					<td>
						<input type="text" size="20" value="' . get_option('ldb_ideal_url') . '" name="ldb_ideal_url" />
					</td>
				</tr>
				<tr>
					<td>
						iDeal urlSuccess <a href="#" title="The URL iDeal should return your customer to after a successful payment, it would be wise to include `?wpsc_ajax_action=empty_cart` at the end of the URL to automatically empty the shopping cart." style="text-decoration: none;">*</a>
					</td>
					<td>
						<input type="text" size="20" value="' .  get_option('ldb_ideal_urlSuccess') . '" name="ldb_ideal_urlSuccess" />
					</td>
				</tr>
				<tr>
					<td>
						iDeal urlCancel <a href="#" title="The URL iDeal should return your customer to after a cancelled payment." style="text-decoration: none;">*</a>
					</td>
					<td>
						<input type="text" size="20" value="' .  get_option('ldb_ideal_urlCancel') . '" name="ldb_ideal_urlCancel" />
					</td>
				</tr>
				<tr>
					<td>
						iDeal urlError <a href="#" title="The URL iDeal should return your customer to after a failed payment." style="text-decoration: none;">*</a>
					</td>
					<td>
						<input type="text" size="20" value="' .  get_option('ldb_ideal_urlError') . '" name="ldb_ideal_urlError" />
					</td>
				</tr>
			</tbody>
		</table>
		<table id="pspid" cellspacing="0" cellpadding="0" class="form-table ldb_wp_ideal_table" style="' . $typetablepspid . '">
			<tbody>
				<tr>
					<td style="width:150px;">
						iDeal PSPID <a href="#" title="Your iDeal PSPID." style="text-decoration: none;">*</a>
					</td>
					<td>
						<input type="text" size="20" value="' . get_option('ldb_ideal_pspid_id') . '" name="ldb_ideal_pspid_id" />
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						iDeal PSPID URL <a href="#" title="Your iDeal PSPID URL." style="text-decoration: none;">*</a>
					</td>
					<td>
						<input type="text" size="20" value="' . get_option('ldb_ideal_pspid_url') . '" name="ldb_ideal_pspid_url" />
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						First name field
					</td>
					<td>
						<select name="ldb_ideal_pspid_first_name" class="ldb_select">' . $pspid_first_name . '</select>
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						Last name field
					</td>
					<td>
						<select name="ldb_ideal_pspid_last_name" class="ldb_select">' . $pspid_last_name . '</select>
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						Email address field
					</td>
					<td>
						<select name="ldb_ideal_pspid_email" class="ldb_select">' . $pspid_email . '</select>
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						Address field
					</td>
					<td>
						<select name="ldb_ideal_pspid_address" class="ldb_select">' . $pspid_address . '</select>
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						Zip/Postal code field
					</td>
					<td>
						<select name="ldb_ideal_pspid_zip" class="ldb_select">' . $pspid_zip . '</select>
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						City field
					</td>
					<td>
						<select name="ldb_ideal_pspid_city" class="ldb_select">' . $pspid_city . '</select>
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						Country field
					</td>
					<td>
						<select name="ldb_ideal_pspid_country" class="ldb_select">' . $pspid_country . '</select>
					</td>
				</tr>
				<tr>
					<td style="width:150px;">
						Phone number field
					</td>
					<td>
						<select name="ldb_ideal_pspid_phone" class="ldb_select">' . $pspid_phone . '</select>
					</td>
				</tr>
			</tbody>
		</table>
		<table id="general" cellspacing="0" cellpadding="0" class="form-table">
			<tbody>
				<tr>
					<td style="width:150px;">
						iDeal Currency <a href="#" title="The currency used for your iDeal transactions." style="text-decoration: none;">*</a>
					</td>
					<td>
						<select name="ldb_ideal_currency">
							<option ' . $currency1 . 'value="EUR">EUR</option>
							<option ' . $currency2 . 'value="USD">USD</option>
							<option ' . $currency3 . 'value="GBP">GBP</option>
						</select>
					</td>
				</tr>
				<tr>
					<td>
						iDeal Language <a href="#" title="The language version of iDeal your customers should be directed to." style="text-decoration: none;">*</a>
					</td>
					<td>
						<select name="ldb_ideal_language">
							<option ' . $language1 . 'value="en_US">English</option>
							<option ' . $language2 . 'value="nl_NL">Dutch</option>
							<option ' . $language3 . 'value="fr_FR">Français</option>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
		<table cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td colspan="2">
						<h2>Message from the developer</h2>
						<p>I\'ve spend quite some time developing the LDB WP e-Commerce iDeal plugin and plan to continue doing so. If you use this plugin please <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MYF3W8N6QWXCJ">donate</a> a token of your appreciation!</p><p style="text-align: center;"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=MYF3W8N6QWXCJ"><img src="' . get_bloginfo('url') . '/wp-content/plugins/ldb-wp-e-commerce-ideal/donate.gif" alt="Donate" style="border: 0;" /></a></p>
					</td>
				</tr>
			</tbody>';

		return $output;
	}
?>