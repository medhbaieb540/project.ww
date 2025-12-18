
<?php
include '../../Controller/eventController.php';
$eventC = new eventController();
$list = $eventC->listevent();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event List</title>
</head>
<body>
    <table border="1">
        <tr>
            <th> ID </th>
            <th> Title </th>
            <th> Description </th>
            <th> Organizer ID </th>
            <th> Event Date </th>
            <th colspan="2">Actions</th>
        </tr>
            <?php
                foreach($list as $event)
                {
            ?>
            <tr>
             <td><?php echo $event['id'];  ?></td>
             <td><?php  echo $event['title'];   ?></td>
             <td><?php  echo $event['description'];   ?></td>
             <td><?php  echo $event['organizer_id'];   ?></td>
             <td><?php  echo $event['eventdate'];   ?></td>
             <td>
                <form method="POST" action="updateEvent.php">
                    <input type="submit" name="update" value="Update">
                    <input type="hidden" value="<?php echo $event['id']; ?>" name="id">
                </form>
            </td>
             <td>
                <a href="deleteEvent.php?id=<?php echo $event['id']; ?>">Delete</a>
             </td>
            </tr>
            <?php
            }
            ?>
   </table>
</body>
</html>


