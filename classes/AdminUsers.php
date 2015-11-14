<?php

/**
 * Created by PhpStorm.
 * User: luke.brown
 * Date: 29/09/2015
 * Time: 16:04
 */
class adminUsers extends siteFunctions
{


    /*
     * Loads all user data from table
     */
    private function getAllUsers() {
        // if database connection opened
        if ($this->databaseConnection()) {

            // load pages for the user
            $sql = $this->db_connection->prepare("SELECT * FROM `users`");
            $sql->execute();

            // fetch all from the widget
            $sql = $sql->fetchAll();

            // $this->debug($sql);

            if ( isset($sql) ) {
                return $sql;
            } else {
               return false;
            }

        } else {

            return false;

        }
    }


    /*
     * Display all of the users data.
     *
     * Checks to see if the data is present and then displays each user
     * with all their data, avatars and the options to edit the data,
     * login as the user and view their login history.
     */
    public function displayUsersTable(){

        $users = $this->getAllUsers();

        if ($users != false) {
            echo '<table class="table table-striped table-hover">
            <thead>
              <tr>
                <th data-sort="int">User ID</th>
                <th data-sort="string">Username</th>
                <th data-sort="string">First Name</th>
                <th data-sort="string">Last Name</th>
                <th data-sort="string">Account Type</th>
                <th data-sort="string">Email Address</th>
                <th data-sort="string">Location</th>
                <th>Options</th>
              </tr>
            </thead>
            <tbody>';

            for ($i = 0; $i < count($users); $i++) {

                if (empty($users[$i]['user_first_name'])) {
                    $firstName = "<i style='opacity:0.5;'>- empty -</i>";
                } else {
                    $firstName = $users[$i]['user_first_name'];
                }

                if (empty($users[$i]['user_last_name'])) {
                    $lastName = "<i style='opacity:0.5;'>- empty -</i>";
                } else {
                    $lastName = $users[$i]['user_last_name'];

                }

                //$location = file_get_contents("http://api.hostip.info/country.php?ip=" . $users[$i]['user_registration_ip']);
                $location = "";

                echo "
                <tr>
                    <td>" . $users[$i]['user_id'] . "</td>
                    <td data-sort-value='" . $users[$i]['user_name'] . "'> <img src='" . $this->getAvatar($users[$i]['user_email']) . "'>" . $users[$i]['user_name'] . "</td>
                    <td>" . $firstName . "</td>
                    <td>" . $lastName . "</td>
                    <td>" . $users[$i]['user_account_type'] . "</td>
                    <td>" . $users[$i]['user_email'] . "</td>
                    <td>" . $location . "</td>
                    <!-- Button trigger modal -->
                    <td>

                        <input type='hidden' name='process' value='loginAsUser'>
                        <input type='hidden' name='user_id' value='" . $users[$i]['user_id'] . "'>
                        <div class='btn-group' role='group' aria-label='...'>
                            <button type='button' class='btn btn-default' data-toggle='modal' data-target='#editUser" . $users[$i]['user_id'] . "'><i class='btl bt-edit'></i> Edit</button>
                            <button type='submit' class='btn btn-default' data-toggle='modal' data-target='#loginAsUser" . $users[$i]['user_id'] . "'><i class='btl bt-key'></i> Log in</button>
                            <button type='button' class='btn btn-default' data-toggle='modal' data-target='#loginHistoryModal" . $users[$i]['user_id'] . "'><i class='btl bt-map-arrow times'></i> History</button>
                        </div>
                    </td>
                </tr>";
            }

            echo "

            </tbody>
          </table>";


            for ($i = 0; $i < count($users); $i++) {
                $this->editUserModal($users[$i]);
                $this->godLoginModal($users[$i]);
                $this->loginHistoryModal($users[$i]);
            }

        } else {
            $this->callbackMessage("<b>Error loading users</b> There was an issue relating to the users database, please check your database settings are correct and working", "danger");
        }


    }

    private function godLoginModal($user) {

        global $_SESSION;


        echo "
            <div class='modal fade' id='loginAsUser" . $user['user_id'] . "' tabindex='-1' role='dialog' aria-labelledby='#loginAsUser" . $user['user_id'] . "'>
                <div class='modal-dialog' role='document'>
                    <form method='post' action='" . $this->url("processing") . "' name='process'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
        ";

        if ($user['user_name'] != $_SESSION['user_name']) {
            echo "
                                <h3 class='modal-title' id='myModalLabel'>Login as " . $user['user_name'] . "</h3>
                                <p>You are about to completely signed out of your current admin account and automatically login as " . $user['user_name'] . ". This feature should be used to test the user experience of the selected user and to find issues they may be facing first hand. </p>
                                <p><b>Warning: you're about to be completely signed out of your user.</b></p>
                            </div>
                            <div class='modal-body'>
                                <div class='row'>
                                    <div class='col-md-4'>
                                        <p>Username</p>
                                    </div>
                                    <div class='col-md-8'>
                                        <input disabled value='" . $user['user_name'] . "' />
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-md-4'>
                                        <p>Password</p>
                                    </div>
                                    <div class='col-md-8'>
                                        <input disabled value='**********' type='password' />
                                    </div>
                                </div>

                            </div>
                            <div class='modal-footer'>
                                <input type='hidden' name='user_id' value='" . $user['user_id'] . "'>
                                <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                                <button type='submit' class='btn btn-black' name='process' value='loginAsUser' class='btn btn-default'>Login as " . $user['user_name'] . "</button>
                            </div>
        ";
        } else {
            echo "
                                <h3 class='modal-title' id='myModalLabel'>Login as " . $user['user_name'] . "</h3>
                                <p>You are already logged in as this user. Try using this feature on another user in this list.</p>
                            </div>
                            <div class='modal-footer'>
                                <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                            </div>
        ";
        }


        echo "

                    </div>
                </form>
            </div>
        </div>
        ";
    }
    private function editUserModal($user){

        global $_SESSION;

        if ($user["user_account_type"] == "admin") {
            $accountType = "
                        <option selected value='admin'>Admin (current)</option>
                        <option value='normal'>Normal</option>
                    ";
        } else {
            $accountType = "
                        <option value='admin'>Admin</option>
                        <option selected  value='normal'>Normal (current)</option>
                    ";
        }

        if ($user['user_name'] == $_SESSION['user_name']) {
            $message = "<b>Be careful! You are editing your own user - User ID #" . $user['user_id'] . "</b>";
        } else {
            $message = "<b>Editing user " . $user['user_name'] . " - User ID #" . $user['user_id'] . "</b>";
        }

        echo "
        <!-- User data modal -->
        <div class='modal fade' id='editUser" . $user['user_id'] . "' tabindex='-1' role='dialog' aria-labelledby='#editUser" . $user['user_id'] . "'>
            <div class='modal-dialog' role='document'>
                <form method='post' action='" . $this->url("processing") . "' name='process'>
                    <div class='modal-content'>
                        <div class='modal-header'>
                            <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                            <h3 class='modal-title' id='myModalLabel'>Detailed Overview for " . $user['user_name'] . "</h3>
                            <p>Here you can override all of the data stored on " . $user['user_name'] . ". Please note, by changing critical information such as the username, email address or password will result in the user not been able to login unless you notify them of the changes.</p>
                            <p>" . $message . "</p>
                        </div>
                        <div class='modal-body'>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Username</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[user_name]' value='" . $user['user_name'] . "' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>First Name</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[user_first_name]' value='" . $user['user_first_name'] . "' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Last Name</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[user_last_name]' value='" . $user['user_last_name'] . "' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Email Address</p>
                                </div>
                                <div class='col-md-8'>
                                    <input name='data[user_email]' value='" . $user['user_email'] . "' />
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Account Type</p>
                                </div>
                                <div class='col-md-8'>
                                    <select name='data[user_account_type]' class='form-control'>
                                          " . $accountType . "
                                    </select>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md-4'>
                                    <p>Password
                                    <input name='data[user_password_box]' style='width:13px; vertical-align: sub' type='checkbox' id='enabledPasswordBox" . $user['user_id'] . "'></p>
                                </div>
                                <div class='col-md-8'>
                                    <input id='passwordBox" . $user['user_id'] . "' type='password' placeholder='enable checkbox to edit' disabled name='data[user_password]'/>
                                </div>
                            </div>

                            <input type='hidden' name='data[user_id]' value='" . $user['user_id'] . "'>
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                            <button type='submit' class='btn btn-black' name='process' value='updateUsersData' class='btn btn-default'>Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
        $( document ).ready(function() {
            $('#enabledPasswordBox" . $user['user_id'] . "').change(function(){
                if ($('#enabledPasswordBox" . $user['user_id'] . "').is(':checked') == true){
                    console.log($('enabledPasswordBox" . $user['user_id'] . "').val());
                    $('#passwordBox" . $user['user_id'] . "').val('').prop('disabled', false);
                } else {
                    $('#passwordBox" . $user['user_id'] . "').val('').prop('disabled', true);
                }

            });
        });
        </script>
        ";
    }
    private function loginHistoryModal($user) {

        global $_SESSION;
        $history = $this->getLoginHistory($user['user_id']);

        echo "
            <div class='modal fade' id='loginHistoryModal" . $user['user_id'] . "' tabindex='-1' role='dialog' aria-labelledby='#loginHistoryModal" . $user['user_id'] . "'>
                <form method='post' action='" . $this->url("processing") . "' name='process'>
                    <div class='modal-dialog modal-lg' role='document'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                                <h3 class='modal-title' id='myModalLabel'>Login history for " . $user['user_name'] . "</h3>
                                <p>This modal display all the login information relating to the user " . $user['user_name'] . ".
                                It can help identify if the account is been hijacked </p>
                            </div>
                            <div class='modal-body'>
        ";

        if (count($history) > 0) {
            echo "

                            <table class='table table-striped table-hover'>
                                <thead>
                                    <tr>
                                        <th data-sort='string'>Date</th>
                                        <th data-sort='string'>Country</th>
                                        <th data-sort='string'>Came from</th>
                                        <th data-sort='string'>Browser</th>
                                        <th data-sort='string'>OS</th>
                                    </tr>
                                </thead>
                                <tbody>
            ";


            for ($i = (count($history) - 1); $i >= 0; $i--) {

                $country_name = $this->ip_info($history[$i]['history_ip'], "Country");
                $country_code = $this->ip_info($history[$i]['history_ip'], "countrycode");
                $browser = $this->getBrowser($history[$i]['history_http_user_agent']);
                $os = $this->getOS($history[$i]['history_http_user_agent']);
                $timeAgo = date('d M H:i', strtotime($history[$i]['history_date']));

                echo "
                                    <tr>
                                        <td data-sort-value='" . $history[$i]['history_date'] . "'>" . $timeAgo . "</td>
                                        <td data-sort-value='" . $country_name . "'> <span class='flag-icon flag-icon-" . $country_code . "'></span> " . $country_name . "</td>
                                        <td>" . $history[$i]['history_http_referer'] . "</td>
                                        <td><i class='btl bt-" . $browser['icon'] . "'></i> " . $browser['browser'] . "</td>
                                        <td><i class='fab fab-" . $os['icon'] . "'></i> " . $os['os'] . "</td>
                                    </tr>";
            }
            echo "
                                </tbody>
                            </table>
            ";

        }
        echo "
                            </div>
                            <div class='modal-footer'>
                                <input type='hidden' name='user_id' value='" . $user['user_id'] . "'>
                                <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        ";
    }

    public function loginAsUser($user_id) {
        global $_SESSION;

        if ($_SESSION['user_account_type'] == "admin") {
            $newUser = $this->getAllUserData($user_id);

            session_destroy();
            session_start();

            $_SESSION['user_id'] = $newUser->user_id;
            $_SESSION['user_name'] = $newUser->user_name;
            $_SESSION['user_email'] = $newUser->user_email;
            $_SESSION['user_first_name'] = $newUser->user_first_name;
            $_SESSION['user_last_name'] = $newUser->user_last_name;
            $_SESSION['user_logged_in'] = 1;
            $_SESSION['user_account_type'] = $newUser->user_account_type;
            $_SESSION['user_display_avatar'] = $newUser->user_display_avatar;
            $_SESSION['user_avatar'] = $newUser->user_avatar;

            $this->callbackMessage("You are now logged in as " . $_SESSION['user_name'] . ", successfully switched accounts.", "success");
            $this->callback();
;        } else {
            $this-> $this->callbackMessage("It appears you do not have permission to perform this action, please contact a site administrator.", "danger");
        }
    }

    /*
     *
     */
    public function updateUsersData($data){

        if ($this->databaseConnection()) {


            if ($data['user_password_box'] == "on") {
                $hash_cost_factor = (defined('HASH_COST_FACTOR') ? HASH_COST_FACTOR : null);
                $user_password_hash = password_hash($data['user_password'], PASSWORD_DEFAULT, array('cost' => $hash_cost_factor));

                $sql = $this->db_connection->prepare('UPDATE `users`
                SET `user_name` = :user_name,
                    `user_first_name` = :user_first_name,
                    `user_last_name` = :user_last_name,
                    `user_password_hash` = :user_password_hash,
                    `use  r_email` = :user_email,
                    `user_account_type` = :user_account_type
                WHERE `user_id` = :user_id');
                $sql->bindValue(':user_password_hash', $user_password_hash, PDO::PARAM_STR);

            } else {

                $sql = $this->db_connection->prepare('UPDATE `users`
                SET `user_name` = :user_name,
                    `user_first_name` = :user_first_name,
                    `user_last_name` = :user_last_name,
                    `user_email` = :user_email,
                    `user_account_type` = :user_account_type
                WHERE `user_id` = :user_id');

            }
            $sql->bindValue(':user_id', $data['user_id'], PDO::PARAM_INT);
            $sql->bindValue(':user_name', $data['user_name'], PDO::PARAM_STR);
            $sql->bindValue(':user_first_name', $data['user_first_name'], PDO::PARAM_STR);
            $sql->bindValue(':user_last_name', $data['user_last_name'], PDO::PARAM_STR);
            $sql->bindValue(':user_email', $data['user_email'], PDO::PARAM_STR);
            $sql->bindValue(':user_account_type', $data['user_account_type'], PDO::PARAM_STR);
            $sql->execute();

            if ($sql->rowCount()) {
                $this->callbackMessage("User <b>" . $data['user_name'] . "</b> has been successfully updated.", "success");
            } else {
                $this->callbackMessage("Failed to update user data belonging to user_id(" . $data['user_id'] . "). It most likely has already been updated.", "info");
            }
        }
    }

    private function getLoginHistory($user_id, $limit = 5){
        // if database connection opened
        if ($this->databaseConnection()) {

            // load pages for the user
            $sql = $this->db_connection->prepare("SELECT * FROM `login_history` WHERE `history_user` = :history_user");
            $sql->bindValue(':history_user', $user_id, PDO::PARAM_INT);
            $sql->execute();

            // fetch all from the widget
            $sql = $sql->fetchAll();

            // $this->debug($sql);

            if ( isset($sql) ) {
                return $sql;
            } else {
                $this->callbackMessage("No results returned", "info");
            }

        } else {

            $this->callbackMessage("Unable to connect to the database to grab login history", "danger");

        }

    }
}
