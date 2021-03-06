<?php
/**
 * Created by PhpStorm.
 * User: Callum
 * Date: 10/05/2017
 * Time: 2:00 PM
 */
require_once("settings.php");

/**
 * This function will display all children that are taught by the logged in teacher in a table.
 */
function teacherDisplayChildren()
{
    $sql = "SELECT s.parentID AS parentID, s.studentNumber AS studentNumber, s.firstName AS firstName, s.lastName AS lastName, t.username AS teacher, s.present AS present  
                FROM student s, enroll e, class c, teacher t
                WHERE t.teacherID = c.teacherID AND c.classID = e.classID AND e.studentID = s.studentID AND t.username LIKE '" . $_SESSION['username'] . "'";
    $result = mysql_query($sql, conn());
    if ($result === FALSE) {
        die(mysql_error());
    }
    echo "<br>Displaying all children at the school in the table below<br>";
    if ($result) {
        /**
         * Creates a table with appropriate column headers.
         */
        echo '<br>
                <table border = "1">
                    <thead>
                        <tr>
                            <th>Parent ID</th>
                            <th>Student Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Teacher</th>
                            <th>Present</th>
                        </tr>
                    </thead>';
        $retrieved = false;
        /**
         * Iterates through all results of the query and populates a new row in the table.
         */
        while ($row = mysql_fetch_array($result)) {
            $retrieved = true;
            $isInSchool = $row['present'];

            /**
             * Checks the value of the "present" variable in the databse. A local present variable has either a
             * Present or Absent value assigned to it.
             */
            if ($isInSchool == 0) {
                $present = "Absent";
            } else {
                $present = "Present";
            }

            /**
             * Echoes the data for the student into a new table row.
             */
            echo "
                    <tr>
                        <td>" . $row['parentID'] . "</td>
                        <td>" . $row['studentNumber'] . "</td>
                        <td>" . $row['firstName'] . "</td>
                        <td>" . $row['lastName'] . "</td>
                        <td>" . $row['teacher'] . "</td>
                        <td>" . $present . "</td>
                    </tr>";
        }
        if ($retrieved == false) {
            echo "
                    <tr>
                        <td colspan=\"5\" style=\"width:100%\" align = \"center\">No students to display</td>
                    </tr>";
        }
        echo '</table><br>';
    }
}

/**
 * This function attempts to update the password of the current teacher based on the session varialbe "id".
 * @param $currentPassword password the teacher typed as their current password
 * @param $newPwd the password the teacher typed as their desired password
 * @param $confirmPwd the password the teacher typed confirming their desired password
 */
function teacherUpdatePassword($currentPassword, $newPwd, $confirmPwd)
{
    $teacherID = $_SESSION['id'];
    $sql = "SELECT * FROM teacher WHERE teacherID = '" . mysql_real_escape_string($teacherID) . "'";
    $result = mysql_query($sql, conn());
    $row = mysql_fetch_array($result);
    $dbPassword = $row['password'];

    /**
     * Checks to see if the current password entered by the user matches the password stored in the database
     */
    if ($dbPassword == $currentPassword) {
        /**
         * Checks to see if the new password and the confirm password variables are the same
         */
        if ($newPwd == $confirmPwd) {
            /**
             * Execute update query which changes the password for the current user.
             */
            $sql = "UPDATE teacher SET password = '" . $newPwd . "' WHERE teacherID = '" . $teacherID . "'";
            $result = mysql_query($sql, conn());
            echo "updated";
        } else {
            echo "Passwords do not match";
        }
    } else {
        echo "Incorrect password";
    }
}

/**
 * This function displays the courses being taught by the current teacher in a table
 */
function showClasses()
{
    $teacherID = $_SESSION['id'];
    $sql = "SELECT c.classCode AS classCode  
                FROM teacher t, class c 
                WHERE t.teacherID = '" . mysql_real_escape_string($teacherID) . "' AND t.teacherID = c.teacherID";
    $result = mysql_query($sql, conn());
    while ($row = mysql_fetch_array($result)) {
        echo "<a href = \"home.php?class=" . $row['classCode'] . "\">" . $row['classCode'] . "</a><br>";
    }
}

/**
 * This function displays the students enrolled in a specific class in table format.
 */
function listStudentsInClass()
{
    /**
     * This first query will return the number of students who are currently marked as present in the database.
     * This number is then printed to the website so that the teacher has a clearer idea of attendance within the class,
     * particulalry in larger classes.
     */
    $sql = "SELECT count(*) as count
            FROM class c, student s, enroll e
            WHERE s.studentID = e.studentID AND e.classID = c.classID AND s.present = 1 AND c.classCode = '" . $_GET['class'] ."'";
    $result = mysql_query($sql, conn()) or die(mysql_error());
    $row = mysql_fetch_array($result);
    $studentsPresent = $row['count'];
    echo "Students Present: " . $studentsPresent . "<br>";

    /**
     * This query returns the total number of students enrolled in a particular class. This is so that the teacher has a number
     * to compare the number of students present to. This is also printed to the website.
     */
    $sql = "SELECT count(*) as count
            FROM class c, student s, enroll e
            WHERE s.studentID = e.studentID AND e.classID = c.classID AND c.classCode = '" . $_GET['class'] ."'";
    $result = mysql_query($sql, conn()) or die(mysql_error());
    $row = mysql_fetch_array($result);
    $totalStudents = $row['count'];
    echo "Total Students: " . $totalStudents;

    /**
     * This query gets the details of students and their current "present" status. The results of the query are presented to the user in a table
     * that lists the student's first and last name, as well as the current course and their present status.
     */
    $sql = "SELECT s.studentID AS studentID, s.studentNumber AS studentNumber, s.firstName AS firstName, s.lastName AS lastName, c.classCode AS classCode, s.present AS present   
                FROM student s, enroll e, class c
                WHERE c.classID = e.classID AND e.studentID = s.studentID AND c.classCode = '" . $_GET['class'] ."'";
    $result = mysql_query($sql, conn()) or die(mysql_error());
    if ($result) {
        echo '<br>
                <table border = "1">
                    <thead>
                        <tr>
                            <th>Student Number</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Course</th>
                            <th>Present</th>
                        </tr>
                    </thead>';
        $retrieved = false;
        while ($row = mysql_fetch_array($result)) {
            $retrieved = true;

            if ($row['present'] == 0) {
                $present = "Absent";
            } else {
                $present = "Present";
            }

            echo "
                    <tr>
                        <td><a href=\"home.php?student=" . $row['studentID'] . "\">" . $row['studentNumber'] . "</a></td>
                        <td>" . $row['firstName'] . "</td>
                        <td>" . $row['lastName'] . "</td>
                        <td>" . $row['classCode'] . "</td>
                        <td>" . $present . "</td>
                    </tr>";
        }
        if ($retrieved == false) {
            echo "
                    <tr>
                        <td colspan=\"5\" style=\"width:100%\" align = \"center\">No students to display</td>
                    </tr>";
        }
        echo '</table><br>';
    }
}

?>