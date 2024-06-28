<?php
// Include database connection file

include('config.php');

// Check if database connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$document = null;

// Handle form submission to fetch document details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_id = intval($_POST['id']);

    // Query to fetch document details
    $sql = "SELECT * FROM `documents` WHERE `document_id` = $document_id";
    $result = $conn->query($sql);

    if ($result) {
        if ($result->num_rows > 0) {
            $document = $result->fetch_assoc();
        } else {
            $error = "Document not found. No records match the given ID.";
        }
    } else {
        $error = "Error executing query: " . $conn->error;
    }
}

// Close database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Document Status</title>
    <link rel="icon" type="image/png" href="image/DTS.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .tracking-container {
            width: 100%;
            max-width: 600px;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .tracking-form, .tracking-details {
            margin-bottom: 20px;
        }
        .tracking-form label {
            display: block;
            margin-bottom: 10px;
        }
        .tracking-form input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .tracking-form input[type="submit"] {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .tracking-form input[type="submit"]:hover {
            background-color: #575757;
        }
        .tracking-details h2 {
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .tracking-details p {
            margin: 10px 0;
        }
        .error {
            color: red;
        }
        .timeline {
            position: relative;
            padding: 20px 0;
            list-style-type: none;
        }
        .timeline:before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #ddd;
            left: 20px;
            margin: 0;
        }
        .timeline-item {
            margin-bottom: 20px;
            position: relative;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #ddd;
            left: 16px;
            top: 0;
        }
        .timeline-item h3 {
            margin: 0 0 5px 40px;
        }
        .timeline-item p {
            margin: 0 0 0 40px;
        }
        .timeline-item.completed:before {
            background: #2196F3;
        }
        .file-links {
            text-align: center;
            margin-top: 5px;
        }
        .file-links a {
            display: inline-block;
            margin: 5px 0;
            color: #2196F3;
            text-decoration: none;
        }
        .file-links a:hover {
            text-decoration: underline;
        }
        .back-to-home-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }
        .back-to-home-button:hover {
            background-color: #0056b3;
        }
        .back-to-home-button:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="tracking-container">
        <h1>Track Document Status</h1><br>
        <div class="tracking-form">
            <form action="track-document-par.php" method="POST">
                <label for="id">Enter Document ID:</label>
                <input type="text" id="id" name="id" required>
                <input type="submit" value="Track Document">
            </form>
        </div>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if ($document): ?>
            <div class="tracking-details">
                <h2>Document Details</h2>
                <p><strong>Document ID:</strong> <?php echo $document['document_id']; ?></p>
                <p><strong>Sender Details:</strong> <?php echo $document['sender_details']; ?></p>
                <p><strong>Date and Time:</strong> <?php echo $document['datetime']; ?></p>
                <p><strong>Document Name:</strong> <?php echo $document['document_name']; ?></p>

                <h2>Current Status</h2>
                <ul class="timeline">
                    <li class="timeline-item <?php echo $document['collected'] ? 'completed' : ''; ?>">
                        <h3>Collected by Operator Assistance (PO)</h3>
                        <p><?php echo $document['collected'] ? date('Y-m-d H:i:s', strtotime($document['collected'])) : 'Not collected'; ?></p>
                    </li>
                    <li class="timeline-item <?php echo $document['checked'] ? 'completed' : ''; ?>">
                        <h3>Checked by Coordinator</h3>
                        <p><?php echo $document['checked'] ? date('Y-m-d H:i:s', strtotime($document['checked'])) : 'Not checked'; ?></p>
                    </li>
                    <li class="timeline-item <?php echo $document['signed'] ? 'completed' : ''; ?>">
                        <h3>Signed by P&I Dean</h3>
                        <p><?php echo $document['signed'] ? date('Y-m-d H:i:s', strtotime($document['signed'])) : 'Not signed'; ?></p>
                        <?php if ($document['signed_file']): ?>
                            <div class="file-links">
                                <a href="uploads/<?php echo $document['signed_file']; ?>" download>Download</a><br>
                                <a href="uploads/<?php echo $document['signed_file']; ?>" target="_blank">View</a>
                            </div>
                        <?php endif; ?>
                    </li>
                    <li class="timeline-item <?php echo $document['sent'] ? 'completed' : ''; ?>">
                        <h3>Sent to RMC</h3>
                        <p><?php echo $document['sent'] ? date('Y-m-d H:i:s', strtotime($document['sent'])) : 'Not sent'; ?></p>
                        <?php if ($document['sent_file']): ?>
                            <div class="file-links">
                                <a href="uploads/<?php echo $document['sent_file']; ?>" download>Download</a><br>
                                <a href="uploads/<?php echo $document['sent_file']; ?>" target="_blank">View</a>
                            </div>
                        <?php endif; ?>
                    </li>
                    <li class="timeline-item <?php echo $document['returned_coordinator'] ? 'completed' : ''; ?>">
                        <h3>Returned to Coordinator</h3>
                        <p><?php echo $document['returned_coordinator'] ? date('Y-m-d H:i:s', strtotime($document['returned_coordinator'])) : 'Not returned'; ?></p>
                    </li>
                    <li class="timeline-item <?php echo $document['returned_sender'] ? 'completed' : ''; ?>">
                        <h3>Returned to Sender</h3>
                        <p><?php echo $document['returned_sender'] ? date('Y-m-d H:i:s', strtotime($document['returned_sender'])) : 'Not returned'; ?></p>
                    </li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
