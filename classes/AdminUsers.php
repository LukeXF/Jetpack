<?php

/**
 * Created by PhpStorm.
 * User: luke.brown
 * Date: 29/09/2015
 * Time: 16:04
 */
class adminUsers extends siteFunctions
{
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
                $this->debug($sql[0]);
                return $sql;
            } else {
               return false;
            }

        } else {

            return false;

        }
    }

    public function displayUsersTable(){

        $users = $this->getAllUsers();

        echo '<table class="table table-striped table-hover">
        <thead>
          <tr>
            <th data-sort="int">User ID</th>
            <th data-sort="string">Username</th>
            <th data-sort="int">First Name</th>
            <th data-sort="string">Last Name</th>
            <th data-sort="string">Account Type</th>
            <th data-sort="string">Email Address</th>
            <th data-sort="string">Location</th>
            <th data-sort="string">Options</th>
          </tr>
        </thead>
        <tbody>';

        for ($i = 0; $i < count($users); $i++) {

            if (empty($users[$i]['user_first_name'])) {
                $users[$i]['user_first_name'] = "<i style='opacity:0.5;'>- empty -</i>";
            }

            if (empty($users[$i]['user_last_name'])) {
                $users[$i]['user_last_name'] = "<i style='opacity:0.5;'>- empty -</i>";
            }

            $location = /* file_get_contents("http://api.hostip.info/country.php?ip=" . $users[$i]['user_registration_ip']); */

            echo "
            <tr>
                <td>" . $users[$i]['user_id'] . "</td> 
                <td data-sort-value='" . $users[$i]['user_name'] . "'> <img src='" . $this->getAvatar($users[$i]['user_email']) . "'>" . $users[$i]['user_name'] . "</td>
                <td>" . $users[$i]['user_first_name'] . "</td>
                <td>" . $users[$i]['user_last_name'] . "</td>
                <td>" . $users[$i]['user_account_type'] . "</td>
                <td>" . $users[$i]['user_email'] . "</td>
                <td>" . $location . "</td>
                <!-- Button trigger modal -->
                <td>
                    <div class='btn-group' role='group' aria-label=''...'>
                      <button type='button' class='btn btn-default' data-toggle='modal' data-target='#editUser" . $users[$i]['user_id'] . "'><i class='btl bt-edit'></i> Edit</button>
                      <button type='button' class='btn btn-default'><i class='btl bt-key'></i> Log in</button>
                      <button type='button' class='btn btn-default'><i class='btl bt-times'></i> Delete</button>
                    </div>
                </td>

                <!-- Modal -->
                <div class='modal fade' id='editUser" . $users[$i]['user_id'] . "' tabindex='-1' role='dialog' aria-labelledby='#editUser" . $users[$i]['user_id'] . "'>
                    <div class='modal-dialog' role='document'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                                <h4 class='modal-title' id='myModalLabel'>Modal #editUser" . $users[$i]['user_id'] . "</h4>
                            </div>
                            <div class='modal-body'>
                                ...
                            </div>
                            <div class='modal-footer'>
                                <button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>
                                <button type='button' class='btn btn-primary'>Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </tr>";
        }

        echo "

        </tbody>
      </table>";



    }
}