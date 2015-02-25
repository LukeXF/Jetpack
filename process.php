<?php
require 'lib/init.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if ( !empty($action) ) {

	switch ( $action ) {

/***********************************************************************************************************************/

		case 'process_payment':


			$status = true;
			$message = '';

			try {

				// make sure we hve hte payment token first
				if ( !post('token') ) {
					throw new Exception('Payment could not be completed, please try again.');
				}

				// build customer data
				$name = post('name');
				$name_arr = explode(' ', trim($name));
				$first_name = $name_arr[0];
				$last_name = trim(str_replace($first_name, '', $name));
				$email = post('email');
				$description = post('description') ? post('description') : 'no description entered';
				$address = post('address');
				$city = post('city');
				$state = post('state');
				$zip = post('zip');
				$country = post('country');

				// check for invoice first
				if ( post('invoice_id') ) {
					$invoice = Model::factory('Invoice')->find_one(post('invoice_id'));
					$amount = $invoice->amount;
					$type = 'invoice';
					$description = $invoice->description;
				// now check for item
				} elseif ( post('item_id') ) {
					$item = Model::factory('Item')->find_one(post('item_id'));
					$amount = $item->price;
					$type = 'item';
				// check for input amount
				} elseif ( post('amount') ) {
					$amount = post('amount');
					$type = 'input';
				// return error if amount not found
				} else {
					throw new Exception('No amount was specified.');
				}

				if ( post('payment_type') == 'recurring' ) {

					// grab list of all plans
					$plans = Stripe_Plan::all();
					// get existing plan if it fits
					$create_plan = true;
					$plan_id = '';
					if ( isset($plans->data) && !empty($plans->data) ) {
						foreach ( $plans->data as $plan ) {
							if ( $plan->interval == 'month' && $plan->amount / 100 == $amount && $plan->interval_count == $config['subscription_interval'] ) {
								// don't match the plan if the trial values don't line up
								if ( ($config['enable_trial'] && $plan->trial_period_days != $config['trial_days']) || (!$config['enable_trial'] && $plan->trial_period_days) ) {
									continue;
								}
								$plan_id = $plan->id;
								$create_plan = false;
								break;
							}
						}
					}
					//create the plan if necessary
					if ( $create_plan ) {
						$plan_arr = array(
							'amount' => $amount * 100,
							'interval_count' => $config['subscription_interval'],
							'interval' => 'month',
							'name' => '$' . $amount . ' every ' . $config['subscription_interval'] . ' month(s)',
							'id' => uniqid(),
							'currency' => $config['currency']
						);
						if ( $config['enable_trial'] && $config['trial_days'] > 0 ) {
							$plan_arr['trial_period_days'] = $config['trial_days'];
							$plan_arr['name'] = $plan_arr['name'] . ' (' . $config['trial_days'] . ' day free trial)';
						}
						$plan = Stripe_Plan::create($plan_arr);
						$plan_id = $plan->id;
					}

					// create the customer
					$customer = Stripe_Customer::create(array(
						'description' => $name,
					));

					// create the subscription
					$subscription_s = $customer->subscriptions->create(array(
						'plan' => $plan_id,
						'card' => post('token')
					));

					$unique_subscription_id = uniqid();
					// save subscription record
					$subscription = Model::factory('Subscription')->create();
					$subscription->unique_id = $unique_subscription_id;
					$subscription->stripe_customer_id = $subscription_s->customer;
					$subscription->stripe_subscription_id = $subscription_s->id;
					$subscription->name = $name;
					$subscription->email = $email;
					$subscription->address = $address;
					$subscription->city = $city;
					$subscription->state = $state;
					$subscription->zip = $zip;
					$subscription->country = $country;
					$subscription->description = isset($item) ? $item->name : $description;
					$subscription->price = $subscription_s->plan->amount / 100;
					$subscription->billing_day = date('j', $subscription_s->start);
					$subscription->length = 0;
					$subscription->interval = $subscription_s->plan->interval_count;
					$subscription->trial_days = $config['enable_trial'] ? $config['trial_days'] : null;
					$subscription->status = 'Active';
					$subscription->date_trial_ends = $config['enable_trial'] ? date('Y-m-d', strtotime('+' . $config['trial_days'] . ' days')) : null;
					$subscription->save();

					// set the message 
					$message = 'Your recurring payment has been created successfully, you should receive a confirmation email shortly.';

				} else {

					// do the payment now
					$transaction = Stripe_Charge::create(array(
					  'amount' => $amount * 100,
					  'currency' => $config['currency'],
					  'card' => post('token'),
					  'description' => isset($item) ? $item->name : $description
					));

					// save payment record
					$payment = Model::factory('Payment')->create();
					$payment->invoice_id = isset($invoice) ? $invoice->id : null;
					$payment->name = $name;
					$payment->email = $email;
					$payment->amount = $transaction->amount / 100;
					$payment->description = isset($item) ? $item->name : $description;
					$payment->address = $address;
					$payment->city = $city;
					$payment->state = $state;
					$payment->zip = $zip;
					$payment->country = $country;
					$payment->type = $type;
					$payment->cc_name = $transaction->card->name;
					$payment->cc_last_4 = $transaction->card->last4;
					$payment->stripe_transaction_id = $transaction->id;
					$payment->save();

					// update paid invoice
					if ( isset($invoice) ) {
						$invoice->status = 'Paid';
						$invoice->date_paid = date('Y-m-d H:i:s');
						$invoice->save();
					}

					// set the message 
					$message = 'Your payment has been completed successfully, you should receive a confirmation email shortly.';

				}


				$trial = isset($subscription) && $subscription->date_trial_ends ? ' <span style="color:#999999;font-size:16px">(Billing starts after your ' . $config['trial_days'] . ' day free trial ends)</span>' : '';
				// build email values first
				$values = array(
					'customer_name' => $name,
					'customer_email' => $email,
					'amount' => currency($amount) . '<small>' . currencySuffix() . '</small>' . $trial,
					'description_title' => isset($item) ? 'Item' : 'Description',
					'description' => isset($item) ? $item->name : $description,
					'payment_method' => 'Credit Card' . (isset($transaction) ? ': XXXX-' . $transaction->card->last4 : ''),
					'transaction_id' => isset($transaction) ? $transaction->id : null,
					'subscription_id' => isset($subscription) ? $subscription->stripe_subscription_id : '',
					'manage_url' => isset($unique_subscription_id) ? url('manage.php?subscription_id=' . $unique_subscription_id) : '',
					'url' => url(''),
				);
				if ( post('payment_type') == 'recurring' ) {
					email($config['email'], 'subscription-confirmation-admin', $values, 'You\'ve received a new recurring payment!');
					email($email, 'subscription-confirmation-customer', $values, 'Thank you for your recurring payment to ' . $config['name']);
				} else {
					email($config['email'], 'payment-confirmation-admin', $values, 'You\'ve received a new payment!');
					email($email, 'payment-confirmation-customer', $values, 'Thank you for your payment to ' . $config['name']);
				}


			} catch (Exception $e) {
				$status = false;
				$message = $e->getMessage();
			}
			

			$response = array(
				'status' => $status,
				'message' => $message
			);
			header('Content-Type: application/json');
			die(json_encode($response));

		break;

/***********************************************************************************************************************/

		case 'paypal_ipn':

			require ('lib/vendor/ipnlistener.php');
			$listener = new IpnListener();

			try {

			    $verified = $listener->processIpn();

			    if ( $verified ) {

			    	// parse our custom field data
					$custom = post('custom');
					if ( $custom ) {
						parse_str(post('custom'), $data);
					} else {
						$data = array();
					}
					// pull out some values
					$payment_gross = post('payment_gross');
					$item_name = post('item_name');

					// build customer data
					$name = isset($data['name']) && $data['name'] ? $data['name'] : null;
					$name_arr = explode(' ', trim($name));
					$first_name = $name_arr[0];
					$last_name = trim(str_replace($first_name, '', $name));
					$email = isset($data['email']) && $data['email'] ? $data['email'] : null;
					$description = $item_name ? $item_name : 'no description entered';
					$address = isset($data['address']) && $data['address'] ? $data['address'] : null;
					$city = isset($data['city']) && $data['city'] ? $data['city'] : null;
					$state = isset($data['state']) && $data['state'] ? $data['state'] : null;
					$zip = isset($data['zip']) && $data['zip'] ? $data['zip'] : null;
					$country = isset($data['country']) && $data['country'] ? $data['country'] : null;

					// check for invoice first
					if ( isset($data['invoice_id']) && $data['invoice_id'] ) {
						$invoice = Model::factory('Invoice')->find_one($data['invoice_id']);
						$amount = $invoice->amount;
						$type = 'invoice';
						$description = $invoice->description;
					// now check for item
					} elseif ( isset($data['item_id']) && $data['item_id'] ) {
						$item = Model::factory('Item')->find_one($data['item_id']);
						$amount = $item->price;
						$type = 'item';
					// check for input amount
					} elseif ( $payment_gross ) {
						$amount = $payment_gross;
						$type = 'input';
					// return error if none found
					} else {
						$amount = 0;
						$type = '';
					}

					switch ( post('txn_type') ) {
						case 'web_accept':

							// save payment record
							$payment = Model::factory('Payment')->create();
							$payment->invoice_id = isset($invoice) ? $invoice->id : null;
							$payment->name = $name;
							$payment->email = $email;
							$payment->amount = $amount;
							$payment->description = isset($item) ? $item->name : $description;
							$payment->address = $address;
							$payment->city = $city;
							$payment->state = $state;
							$payment->zip = $zip;
							$payment->country = $country;
							$payment->type = $type;
							$payment->paypal_transaction_id = post('txn_id');
							$payment->save();

							// update paid invoice
							if ( isset($invoice) ) {
								$invoice->status = 'Paid';
								$invoice->date_paid = date('Y-m-d H:i:s');
								$invoice->save();
							}

							// build email values first
							$values = array(
								'customer_name' => $payment->name,
								'customer_email' => $payment->email,
								'amount' => currency($payment->amount) . '<small>' . currencySuffix() . '</small>',
								'description_title' => isset($item) ? 'Item' : 'Description',
								'description' => $payment->description,
								'payment_method' => 'PayPal',
								'url' => url(''),
							);
							email($config['email'], 'payment-confirmation-admin', $values, 'You\'ve received a new payment!');
							email($payment->email, 'payment-confirmation-customer', $values, 'Thank you for your payment to ' . $config['name']);

						break;

						case 'subscr_signup':

							try {
							
								$unique_subscription_id = uniqid();
								// save subscription record
								$subscription = Model::factory('Subscription')->create();
								$subscription->unique_id = $unique_subscription_id;
								$subscription->paypal_subscription_id = post('subscr_id');
								$subscription->name = $name;
								$subscription->email = $email;
								$subscription->address = $address;
								$subscription->city = $city;
								$subscription->state = $state;
								$subscription->zip = $zip;
								$subscription->country = $country;
								$subscription->description = isset($item) ? $item->name : $description;
								$subscription->price = post('amount3');
								$subscription->billing_day = date('j', strtotime(post('subscr_date')));
								$subscription->length = $config['subscription_length'];
								$subscription->interval = $config['subscription_interval'];
								$subscription->trial_days = $config['enable_trial'] ? $config['trial_days'] : null;
								$subscription->status = 'Active';
								$subscription->date_trial_ends = $config['enable_trial'] ? date('Y-m-d', strtotime('+' . $config['trial_days'] . ' days')) : null;
								$subscription->save();

								$trial = $subscription->date_trial_ends ? ' <span style="color:#999999;font-size:16px">(Billing starts after your ' . $config['trial_days'] . ' day free trial ends)</span>' : '';
								$values = array(
									'customer_name' => $name,
									'customer_email' => $email,
									'amount' => currency(post('amount3')) . '<small>' . currencySuffix() . '</small>' . $trial,
									'description_title' => isset($item) ? 'Item' : 'Description',
									'description' => isset($item) ? $item->name : $description,
									'payment_method' => 'PayPal',
									'subscription_id' => post('subscr_id'),
									'manage_url' => url('manage.php?subscription_id=' . $unique_subscription_id)
								);
								email($config['email'], 'subscription-confirmation-admin', $values, 'You\'ve received a new recurring payment!');
								email($email, 'subscription-confirmation-customer', $values, 'Thank you for your recurring payment to ' . $config['name']);

							} catch (Exception $e) {

							}

						break;

						case 'subscr_cancel':
							$subscription = Model::factory('Subscription')->where('paypal_subscription_id', post('subscr_id'))->find_one();
							if ( $subscription ) {
								$subscription->status = 'Canceled';
								$subscription->date_canceled = date('Y-m-d H:i:s');
								$subscription->save();
								// send subscription cancelation email now
								$values = array(
									'customer_name' => $subscription->name,
									'customer_email' => $subscription->email,
									'amount' => currency($subscription->price) . '<small>' . currencySuffix() . '</small>',
									'description' => $subscription->description,
									'payment_method' => 'PayPal',
									'subscription_id' => $subscription->paypal_subscription_id
								);
								email($config['email'], 'subscription-canceled-admin', $values, 'A recurring payment has been canceled.');
								email($subscription->email, 'subscription-canceled-customer', $values, 'Your recurring payment to ' . $config['name'] . ' has been canceled.');
							}
						break;

						case 'subscr_eot':
							$subscription = Model::factory('Subscription')->where('paypal_subscription_id', post('subscr_id'))->find_one();
							if ( $subscription && $subscription->status == 'Active' ) {
								$subscription->status = 'Expired';
								$subscription->date_canceled = null;
								$subscription->save();
							}
						break;

					}

				

				} else {
					die();
				}


			} catch (Exception $e) {
				die();
			}

		break;

/***********************************************************************************************************************/

		case 'paypal_success':
			go('index.php#status=paypal_success');
		break;

/***********************************************************************************************************************/

		case 'paypal_subscription_success':
			go('index.php#status=paypal_subscription_success');
		break;

/***********************************************************************************************************************/

		case 'paypal_cancel':
			msg('You canceled your PayPal payment, no payment has been made.', 'warning');
			go('index.php');
		break;

/***********************************************************************************************************************/

		case 'delete_payment':
			if ( isset($_GET['id']) ) {
				$payment = Model::factory('Payment')->find_one($_GET['id']);
				$payment->delete();
			}
			msg('Payment has been deleted successfully.', 'success');
			go('admin.php#tab=payments');
		break;

/***********************************************************************************************************************/

		case 'delete_subscription':
			if ( isset($_GET['id']) ) {
				$subscription = Model::factory('Subscription')->find_one($_GET['id']);
				$subscription->delete();
			}
			msg('Subscription has been deleted successfully.', 'success');
			go('admin.php#tab=subscriptions');
		break;

/***********************************************************************************************************************/

		case 'cancel_subscription':
			if ( isset($_GET['subscription_id']) ) {
				$subscription = Model::factory('Subscription')->find_one($_GET['subscription_id']);
				$subscription->status = 'Canceled';
				$subscription->date_canceled = date('Y-m-d H:i:s');
				$subscription->save();
				try {
					if ( $subscription->stripe_customer_id && $subscription->stripe_subscription_id ) {


						$customer = Stripe_Customer::retrieve($subscription->stripe_customer_id);
						$subscription_s = $customer->subscriptions->retrieve($subscription->stripe_subscription_id);
						$subscription_s->cancel();
						
						// send subscription cancelation email now
						$values = array(
							'customer_name' => $subscription->name,
							'customer_email' => $subscription->email,
							'amount' => currency($subscription->price) . '<small>' . currencySuffix() . '</small>',
							'description' => $subscription->description,
							'payment_method' => 'Credit Card',
							'subscription_id' => $subscription->stripe_subscription_id
						);
						email($config['email'], 'subscription-canceled-admin', $values, 'A recurring payment has been canceled.');
						email($subscription->email, 'subscription-canceled-customer', $values, 'Your recurring payment to ' . $config['name'] . ' has been canceled.');
					}
				} catch (Stripe_CardError $e) {
				} catch (Stripe_InvalidRequestError $e) {
				} catch (Stripe_AuthenticationError $e) {
				} catch (Stripe_ApiConnectionError $e) {
				} catch (Stripe_Error $e) {
				} catch (Exception $e) {
					$error = $e->getMessage();
				}
			}
			if ( !isset($_GET['prevent_msg']) ) {
				if ( isset($error) ) {
					msg($error, 'danger');
				} else {
					msg('Your subscription has been canceled successfully.', 'success');
				}
			}
			if ( get('return') == 'admin' ) {
				go('admin.php#tab=subscriptions');
			} else {
				go('manage.php?subscription_id=' . $subscription->unique_id);
			}
		break;

/***********************************************************************************************************************/

		case 'create_invoice':
			if ( post('email') && post('amount') && post('description') ) {
				$unique_invoice_id = uniqid();
				$invoice = Model::factory('Invoice')->create();
				$invoice->unique_id = $unique_invoice_id;
				$invoice->email = post('email');
				$invoice->description = post('description');
				$invoice->amount = post('amount');
				$invoice->number = post('number');
				$invoice->status = 'Unpaid';
				$invoice->date_due = post('date_due') ? date('Y-m-d', strtotime(post('date_due'))) : null;
				$invoice->save();
			}
			$number = $invoice->number ? $invoice->number : $invoice->id();
			if ( post('send_email') && post('send_email') ) {
				$values = array(
					'number' => $number,
					'amount' => currency($invoice->amount) . '<small>' . currencySuffix() . '</small>',
					'description' => $invoice->description,
					'date_due' => !is_null($invoice->date_due) ? date('F jS, Y', strtotime($invoice->date_due)) : '<em>no due date set</em>',
					'url' => url('?invoice_id=' . $unique_invoice_id)
				);
				email($invoice->email, 'invoice', $values, 'Invoice from ' . $config['name']);
				$msg = ' and sent';
			}
			msg('Invoice has been created' . (isset($msg) ? $msg : '') . ' successfully.', 'success');
			go('admin.php#tab=invoices');
		break;

/***********************************************************************************************************************/

		case 'delete_invoice':
			if ( isset($_GET['id']) ) {
				$invoice = Model::factory('Invoice')->find_one($_GET['id']);
				$invoice->delete();
			}
			msg('Invoice has been deleted successfully.', 'success');
			go('admin.php#tab=invoices');
		break;

/***********************************************************************************************************************/

		case 'add_item':
			if ( post('name') && post('price') ) {
				$item = Model::factory('Item')->create();
				$item->name = post('name');
				$item->price = post('price');
				$item->save();
			}
			msg('Item has been added successfully.', 'success');
			go('admin.php#tab=items');
		break;

/***********************************************************************************************************************/

		case 'edit_item':
			if ( post('id') && post('name') && post('price') ) {
				$item = Model::factory('Item')->find_one(post('id'));
				$item->name = post('name');
				$item->price = post('price');
				$item->save();
			}
			msg('Item has been edited successfully.', 'success');
			go('admin.php#tab=items');
		break;

/***********************************************************************************************************************/

		case 'delete_item':
			if ( isset($_GET['id']) ) {
				$item = Model::factory('Item')->find_one($_GET['id']);
				$item->delete();
			}
			msg('Item has been deleted successfully.', 'success');
			go('admin.php#tab=items');
		break;

/***********************************************************************************************************************/

		case 'save_config':
			if ( post('config') && is_array(post('config')) ) {
				foreach ( post('config') as $key => $value ) {
					$config = Model::factory('Config')->where('key', $key)->find_one();
					if ( $config ) {
						$config->value = $value;
						$config->save();
					}
				}
			}
			msg('Your settings have been saved successfully.', 'success');
			go('admin.php#tab=settings');
		break;

/***********************************************************************************************************************/

		case 'login':
			if ( 
				post('admin_username') && post('admin_username') == $config['admin_username'] && 
				post('admin_password') && post('admin_password') == $config['admin_password']
			) {
				// login successful, set session
				$_SESSION['admin_username'] = $config['admin_username'];
			} else {
				// login failed, set error message
				msg('Login attempt failed, please try again.', 'danger');
			}
			go('admin.php');
		break;

/***********************************************************************************************************************/

		case 'logout':
			unset($_SESSION['admin_username']);
			session_destroy();
			session_start();
			msg('You have been logged out successfully.', 'success');
			go('admin.php');
		break;

/***********************************************************************************************************************/

		case 'install':
			$status = true;
			$message = '';
			try {
				$db = new PDO('mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'], $config['db_username'], $config['db_password']);
				$sql = file_get_contents('lib/sql/install.sql');
				$result = $db->exec($sql);
			} catch (PDOException $e) {
				$status = false;
				$message = $e->getMessage();
			}
			$response = array(
				'status' => $status,
				'message' => $message
			);
			header('Content-Type: application/json');
			die(json_encode($response));
		break;

/***********************************************************************************************************************/


	}

}