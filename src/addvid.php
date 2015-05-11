<?php
//removing delete params after deleting everything
if(isset($_GET["delete"]) && $_GET["delete"] == "all" ){
   header('location: http://web.engr.oregonstate.edu/~barrymin/addvid.php');
}
echo '<head><link rel="stylesheet" href="style2.css"></head>';
error_reporting(E_ALL);
ini_set('didspllay_errors','On');

//connect to server
$mysqli = new mysqli("oniddb.cws.oregonstate.edu", "barrymin-db", "kBL9VlPsjkWf9PIW", "barrymin-db");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
    
//case where form is submitted to add a video
if(isset($_POST['vid-name']) && isset($_POST['vid-category'])
    && isset($_POST['vid-length']) && ($_POST['vid-length'] >=0) ){
	if($_POST['vid-name']!= "") {
		
	    if (!($stmt = $mysqli->prepare("INSERT INTO Videos(name, category, length, rented) VALUES (?,?,?,0)"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        }
	    if (!$stmt->bind_param("ssi", $_POST['vid-name'],$_POST['vid-category'],$_POST['vid-length'])) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }

        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->close();
	} else {
        if ($_POST['vid-name'] == "") {
			echo "Name must not be empty.";
		}
	}
} else{
    if(isset($_POST['vid-length']) && $_POST['vid-length'] <= 0){
        echo "You must enter a positive length.";
    }
}
//delete all videos
if(isset($_GET["delete"]) && $_GET["delete"] == "all"){
    if (!($stmt = $mysqli->prepare("DELETE FROM Videos"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    $stmt->close();
	header( 'Location: http://web.engr.oregonstate.edu' ) ;
   
} 
//delete a video
if(isset($_GET["delete"]) && $_GET["delete"] != "all" && $_GET["delete"] != "" ){
    if (!($stmt = $mysqli->prepare("DELETE FROM Videos WHERE id = ?"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmt->bind_param("i", $_GET["delete"])) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    $stmt->close();
	//unset params	
}
//rent video
if(isset($_GET["rent"]) && $_GET["rent"] != "" ){
    if (!($stmt = $mysqli->prepare("UPDATE Videos SET rented = 1 WHERE id = ?"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmt->bind_param("i", $_GET["rent"])) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    $stmt->close();
	//unset params	
}
//return video
if(isset($_GET["unrent"]) && $_GET["unrent"] != "" ){
    if (!($stmt = $mysqli->prepare("UPDATE Videos SET rented = 0 WHERE id = ?"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmt->bind_param("i", $_GET["unrent"])) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    $stmt->close();
	//unset params	
}
?>

<form  method="post">
<label>Name: </label>
<input type="text" name="vid-name" required="required"/>
<label>Category</label>
<input type="text" name="vid-category"/>
<label>Length: </label>
<input type="number" name="vid-length"/>
<button type="submit">ADD</button>
</form>

<?php
/*filter form*/
//prepare
if (!($stmt = $mysqli->prepare("SELECT DISTINCT category FROM Videos WHERE category <> ''"))) {
    echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
}
//execute
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
$category=NULL;
//bind result
if (!$stmt->bind_result($category)) {
    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}
//display form
echo "<form> <select name='select'><option value='all'>All</option>";
//add options to form
while($stmt->fetch()){
    echo '<option value="'.$category.'">'.$category.'</option>';
}
//close statment
$stmt->close();
//close form
echo "</select><button type='submit'>Filter</button></form>";


/*get videos data*/
//filtered
if (isset($_GET["select"]) && $_GET["select"] != "all") {
    if (!($stmt = $mysqli->prepare("SELECT id,name,category,length,rented FROM Videos WHERE category = ?"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
    if (!$stmt->bind_param("s", $_GET['select'])) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
} else {
    if (!($stmt = $mysqli->prepare("SELECT id,name,category,length,rented FROM Videos"))) {
        echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
    }
}
if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
}
$id=NULL;
$name= NULL;
$length=NULL;
$rented=NULL;

if (!$stmt->bind_result($id,$name, $category, $length,$rented)) {
    echo "Binding output parameters failed: (" . $stmt->errno . ") " . $stmt->error;
}

//display table
echo "<table>";
while($stmt->fetch()) {
    echo "<tr>";
    echo "<td>$name</td>";
	echo "<td>$category</td>";
	echo "<td>$length</td>";
	if($rented){
        echo"<td>Checked out</td>";
        echo "<td><form><button type=submit name='unrent' value='$id'>Return</button></form></td>";
	}else {
        echo"<td>Available</td>";
        echo "<td><form><button type=submit name='rent' value='$id'>Rent</button></form></td>";
	}
	echo "<td><form><button type=submit name='delete' value='$id'>Delete</button></form></td>"; 
}
/*Delete all button*/
echo "<form><button name='delete' value='all'>Delete All</button></form>";
?>