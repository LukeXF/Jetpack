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
                $this->debug($sql);
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


        for ($i = 0; $i <= count($users); $i++) {
            echo "The number is: $i<br>";
        }

        echo '<table>
        <thead>
          <tr>
            <th data-sort="int">int</th>
            <th data-sort="float">float</th>
            <th >string</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>15</td>
            <td>-.18</td>
            <td>banana</td>
          </tr>
          <tr class="awesome">
            <td>95</td>
            <td>36</td>
            <td>coke</td>
          </tr>
          <tr>
            <td>2</td>
            <td>-152.5</td>
            <td>apple</td>
          </tr>
          <tr>
            <td>-53</td>
            <td>88.5</td>
            <td>zebra</td>
          </tr>
          <tr>
            <td>195</td>
            <td>-858</td>
            <td>orange</td>
          </tr>
        </tbody>
      </table>';
    }
}