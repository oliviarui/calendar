<?php

    // pass csrf tokens
    // use htmlentities or htmlspecialcharacters PLUS pregmatch to sanitize on the client side

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Calendar</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
        <script src="https://classes.engineering.wustl.edu/cse330/content/calendar.min.js"></script>
        <script src="calendar.js"></script>
        <link rel="stylesheet" type="text/css" href="calendar.css">
    </head>
    <body>
        <nav>
            <p class="nav-item" id="welcome-message"></p>
            <button id="sign-out-button" class="button">Sign out</button>
        </nav>

        <div id="sign-in-box" class="box">
            <h1>Sign In Below</h1>
            <label for="username-input">Enter your username</label>
            <input type="text" name="username" id="username-input">
                <br>
            <label for="password-input">Enter your password</label>
            <input type="password" name="password" id="password-input">
                <br>
            <button id="sign-in-button">Sign In</button>
                <br>
            <button id="register-redirect">Register</button>
        </div>

        <div id="register-box" class="box">
            <h1>Register Below</h1>
            <label for="new-username">Enter a username</label>
            <input type="text" name="new-user" id="new-username">
            <br>
            <label for="new-password">Enter a password</label>
            <input type="password" name="new-password" id="new-password">
            <br>
            <button id="register-button">Register</button>
            <br>
            <button id="sign-in-redirect">Sign In</button>
        </div>

        <div id="main" class="box">
            <button id="prev" class="button">Previous month</button>
            <button id="next" class="button">Next month</button>
            <table id="calendar">
                <thead>
                    <tr>
                        <th>Sunday</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                        <th>Saturday</th>
                    </tr>
                </thead>
                <tbody id="calendar-body">
                </tbody>
            </table>

            <div id="pop-up" class="pop-up-box">
                <div id="event-creator">
                    <label for="event-title">Event title</label>
                    <input type="text" id="event-title">
                    <br>
                    <label for="event-date">Event date</label>
                    <input type="date" id="event-date">
                    <br>
                    <label for="event-time">Event time</label>
                    <input type="time" id="event-time">
                    <br>
                    <p>Event Importance</p>
                    <input type="radio" id="import-high" name="import">
                    <label for="import-high">High</label>
                    <input type="radio" id="import-med" name="import">
                    <label for="import-med">Medium</label>
                    <input type="radio" id="import-low" name="import">
                    <label for="import-low">Low</label>
                    <br>
                    <label for="event-descript">Event description</label>
                    <textarea id="event-descript" rows="10" cols="20" wrap="soft"></textarea>
                    <br>
                    <button id="event-button">Create event</button>
                </div>
                <div id="event-details">
                    <h3 id="title">Event Title</h3>
                    <p id="time"></p>
                    <p id="importance"></p>
                    <p id="shared"></p>
                    <pre id="descript"></pre>
                    <button id="edit">Edit</button>
                    <button id="delete">Delete</button>
                    <button id="share">Share</button>
                </div>
                <div id="event-editor">
                    <label for="edit-event-title">Event title</label>
                    <input type="text" id="edit-event-title">
                    <br>
                    <label for="edit-event-date">Event date</label>
                    <input type="date" id="edit-event-date">
                    <br>
                    <label for="edit-event-time">Event time</label>
                    <input type="time" id="edit-event-time">
                    <br>
                    <p>Event Importance</p>
                    <input type="radio" id="edit-import-high" name="edit-import">
                    <label for="edit-import-high">High</label>
                    <input type="radio" id="edit-import-med" name="edit-import">
                    <label for="edit-import-med">Medium</label>
                    <input type="radio" id="edit-import-low" name="edit-import">
                    <label for="edit-import-low">Low</label>
                    <br>
                    <label for="edit-event-descript">Event description</label>
                    <textarea id="edit-event-descript" rows="10" cols="20" wrap="soft"></textarea>
                    <br>
                    <button id="save-event-button">Save</button>
                </div>
                <div id="event-share">
                    <p>Share your event</p>
                    <label for="share-user">Type in your friend's username</label>
                    <input type="text" id="share-user">
                    <br>
                    <button id="share-event-button">Share</button>
                </div>
                <button id="event-close-button">Close</button>
            </div>
        </div>

    </body>
</html>