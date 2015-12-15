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
            <th data-sort="string">Email</th>
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

            echo "
            <tr>
                <td>" . $users[$i]['user_id'] . "</td> 
                <td data-sort-value='" . $users[$i]['user_name'] . "'> <img src='" . $this->getAvatar($users[$i]['user_email']) . "'>" . $users[$i]['user_name'] . "</td>
                <td>" . $users[$i]['user_first_name'] . "</td>
                <td>" . $users[$i]['user_last_name'] . "</td>
                <td>" . $users[$i]['user_email'] . "</td>
            </tr>";
        }

        echo "

        </tbody>
      </table>";



    }
}