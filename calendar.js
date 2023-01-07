document.addEventListener("DOMContentLoaded", main); // how to do this in jQuery, tried with load but didn't work
// NOTE: for some reason, jQuery does not work outside of a function?

let date = new Date();
let monthObj = new Month(date.getFullYear(), date.getMonth());
let currentEvent;
let csrfToken;

function main() {
    // reset everything non-calendar related
    reset();

    // check if user is logged in
    fetch('login-status.php')
    .then(res => res.json())
    .then(function(jsonData) { 
        if(jsonData.loggedIn) { 
            // display calendar and nav bar to logged in user
            $('nav').css({'display':'block'});
            $('#main').css({'display':'block'});
            // set csrf token
            csrfToken = jsonData.token;
            // set welcome message
            $('#welcome-message').html('Welcome ' + jsonData.username);
            setCalendarButtons();
            // render calendar
            renderCalendar();
        } else { // display sign in page to not logged in user
            $('#sign-in-box').css({'display':'block'});
        }
    });
    setBasicButtons();
}

function reset() {
    // clear page
    $('#sign-in-box').css({'display':'none'});
    $('#register-box').css({'display':'none'});
    $('#main').css({'display':'none'});
    $('#pop-up').css({'display':'none'});
    $('nav').css({'display':'none'});

    // clear input values for register and sign in
    $("#new-username").val('');
    $("#new-password").val('');
    $("#username-input").val('');
    $("#password-input").val('');

    //remove all event listeners
    $('#sign-out-button').off('click');
    $('#sign-in-button').off('click');
    $('#register-button').off('click');
    $('#event-button').off('click');
    $('#event-close-button').off('click');
    $('#delete').off('click');
    $('#edit').off('click');
    $('#save-event-button').off('click');
    $('#share').off('click');
    $('#share-event-button').off('click');

    $('#register-redirect').off('click');
    $('#sign-in-redirect').off('click');

    $('#prev').off('click');
    $('#next').off('click');
}

function setBasicButtons() {
    // set button event listeners
    $('#sign-out-button').on('click', signOut);
    $('#sign-in-button').on('click', signIn);
    $('#register-button').on('click', register);
    $('#event-button').on('click', createEvent);
    $('#delete').on('click', eventDelete);
    $('#edit').on('click', eventEdit);
    $('#save-event-button').on('click', eventUpdate);
    $('#share').on('click', renderShare);
    $('#share-event-button').on('click', eventShare);


    // add redirect event listeners to switch between sign in and register
    $('#register-redirect').on('click', function() {
        $('#sign-in-box').css({'display':'none'});
        $('#register-box').css({'display':'block'});
    });
    $('#sign-in-redirect').on('click', function() {
        $('#register-box').css({'display':'none'});
        $('#sign-in-box').css({'display':'block'});
    });
    $('#event-close-button').on('click', closeEvent);
}

function clearPopUpDisplay() {
    $('#event-creator').css({'display':'none'});
    $('#event-details').css({'display':'none'});
    $('#event-editor').css({'display':'none'});
    $('#event-share').css({'display':'none'});
}

function closeEvent() {
    // set display of everything to none
    $('#pop-up').css({'display':'none'});
    clearPopUpDisplay();
    
    // clear input values for creating an event
    $("#event-title").val('');
    $("#event-date").val('');
    $("#event-time").val('');
    $("#event-descript").val('');
    document.getElementById('import-high').checked = false;
    document.getElementById('import-med').checked = false;
    document.getElementById('import-low').checked = false;

    // clear input values for sharing an event
    $("#share-user").val('');

    // do not need to clear input values for editing because those will always be overwritten
    // before being displayed to the user
    console.log(currentEvent);
}

function renderShare() {
    clearPopUpDisplay();
    $('#event-share').css({'display':'block'});
}

// main functionality

function register() {
    // get input using jquery
    const newUser = $("#new-username").val();
    const newPass = $("#new-password").val();
    
    // filter input to give user feedback (input will be filtered again on the server side)
    if(/^$/.test(newUser) || /^$/.test(newPass)) { 
        // either username or password is empty
        alert('Cannot have empty username or password. Try again.');
    } else {
        // username and password are both not empty
        if(/^[a-zA-Z0-9_.-]*$/.test(newUser)) {
            // valid username, send to backend using json

            // create data to be sent
            const data = {user : newUser, pass : newPass};

            // send data
            fetch('register.php',{
            method: 'POST',  // method to send data
            headers: {
                'Content-Type': 'application/json' // specify that this will be a json data type
            },
            body: JSON.stringify(data) // turn data into json format
            })
            .then(res => res.json()) // this is getting the data
            .then(function(jsonData) { // do something with the data
                if(jsonData.success) {
                    alert('Successfully registered! ヾ(≧▽≦*)o');
                    main();
                } else {
                    alert('Registration failed. Please try again.');
                }
            });   
        } else {
            // invalid username
            alert('Username cannot have special characters other than "_", "-", and "."');
        }
    }
}

function signIn() {
    // get input using jquery
    const inputUser = $("#username-input").val();
    const inputPass = $("#password-input").val();
    
    // filter input to give user feedback (input will be filtered again on the server side)
    if(/^$/.test(inputUser) || /^$/.test(inputPass)) { 
        // either username or password is empty
        alert('Please enter a username and password');
    } else {
        // username and password are both not empty, send to backend using json

        // create data to be sent
        const data = {user : inputUser, pass : inputPass};

        // send data
        fetch('sign-in.php',{
        method: 'POST',  // method to send data
        headers: {
            'Content-Type': 'application/json' // specify that this will be a json data type
        },
        body: JSON.stringify(data) // turn data into json format
        })
        .then(res => res.json()) 
        .then(function(jsonData) { 
            if(jsonData.success) {
                console.log(jsonData.success);
                // call main function to check login status again and display calendar
                main(); 
            } else {
                alert('Sign in failed. Please try again.');
            }
        })   
    }
}

function signOut() {
    fetch('logout.php')
    .then(res => res.json())
    .then(function(jsonData) { 
        if(jsonData.success) { 
            main(); // TODO: this is calling render calendar twice?
        } else { // display sign in page to not logged in user
            $('#sign-in-box').css({'display':'block'});
        }
    });
}

function createEvent() {
    // get input using jquery
    const eventTitle = $('#event-title').val();
    const eventDate = $("#event-date").val();
    let eventTime = $('#event-time').val();
    const eventDescript = $("#event-descript").val();
    // get importance inputs
    const importanceIDs = ['import-high','import-med','import-low'];
    const importance = [];
    importanceIDs.forEach(function(id) {
        importance.push(document.getElementById(id));
    });

    // input will be checked again on the server side
    if(/^$/.test(eventTitle) || /^$/.test(eventDate) || /^$/.test(eventTime)) { 
        // one of the required fields is empty
        alert('Please make sure you have entered a title, date, and time.');
    } else {
        // create event datetime variable
        eventTime = eventTime + ':00';

        let inputImport;
        if(importance[0].checked == true) {
            inputImport = 'h';
        } else if (importance[1].checked == true) {
            inputImport = 'm';
        } else if (importance[2].checked == true) {
            inputImport = 'l';
        }
        
        // create data to be sent
        let data;
        if(inputImport) { // dataset to be sent if there is an importance value
            data = {token : csrfToken, title : eventTitle, date : eventDate, time : eventTime, descript : eventDescript, import : inputImport};
        } else { // dataset to be sent if there is not an importance value
            data = {token : csrfToken, title : eventTitle, date : eventDate, time : eventTime, descript : eventDescript};
        }        

        // send data
        fetch('create-event.php',{
        method: 'POST',  // method to send data
        headers: {
            'Content-Type': 'application/json' // specify that this will be a json data type
        },
        body: JSON.stringify(data) // turn data into json format
        })
        .then(res => res.json())
        .then(function(jsonData) { 
            if(jsonData.success) {
                // successfully inserted event into events table
                // next we need to pair the event with the user in the event permissions table

                if(/^\d+$/.test(jsonData.eventID)) {
                    // input field is not empty, add event user pair to the permissions table
                    // create data set containing user id and event id
                    const data = {token : csrfToken, event : jsonData.eventID};
                    fetch('event-user-pair.php',{
                    method: 'POST',  // method to send data
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                    })
                    .then(res => res.json())
                    .then(function(jsonData) { 
                        if(jsonData.success) {
                            closeEvent();
                            renderCalendar();
                        } else {
                            alert('Failed to pair event with user ID');
                            console.log(jsonData.erorr);
                        }
                    })
                } 
            } else {
                alert('Failed to create event.');
            }
        })
    }
}

// display calendar

function renderCalendar() {
    // clear all elements in the calendar except the headers
    $('#calendar-body').empty();
    $('#month-year-label').remove();
    $('#event-creator').css({'display':'none'});

    // display today's date
    // $('#calendar').append('<p>'+date.toDateString()+'</p>');

    // display month and year
    $('#calendar').before('<p id="month-year-label">'+ date.toLocaleString("en-US", { month: "long" }) + ', ' + date.getFullYear() +'</p>');

    const weeks = monthObj.getWeeks();

    //render each week
    for (let i = 0; i < weeks.length; i++) {
        let week = weeks[i].getDates()
        $('#calendar-body').append('<tr id="week'+i+'"></tr>');
        for(let j = 0; j < week.length; j++) {
            let day = week[j];
            if(day.getMonth() == monthObj.month) {
                // the current day is in the month
                $('#week'+i).append('<td class="day in-month" id="'+day.getDate()+'">'+day.getDate()+'</td>');
                // set event listener to bring up event creating page
                $('#'+day.getDate()).on('click', function() {
                    $('#pop-up').css({'display':'block'});
                    clearPopUpDisplay();
                    // set calendar to automatically fill in the date that was clicked on
                    $('#event-date').val(date.getFullYear()+'-'+(date.getMonth()+1)+'-'+day.getDate());
                    $('#event-creator').css({'display':'block'});
                });
            } else {
                // the current day is not in the month
                $('#week'+i).append('<td class="day">'+day.getDate()+'</td>');
            }
        }
    }

    //render events
    renderEvents();
}

// render all events for the current calendar
function renderEvents() {
    // only check for events of the current month
    $('.in-month').each(function() {
        const currentCell = $(this);

        // create string of current date
        const currentDate = date.getFullYear()+'-'+(date.getMonth()+1)+'-'+$(this).attr("id");

        // create data to be sent
        // DEBUG: token
        // console.log(csrfToken);
        const data = {token : csrfToken, date : currentDate};

        // send data
        fetch('get-event.php',{
            method: 'POST',  // method to send data
            headers: {
                'Content-Type': 'application/json' // specify that this will be a json data type
            },
            body: JSON.stringify(data) // turn data into json format
            })
            .then(res => res.json())
            .then(function(jsonData) { 
                if(jsonData.hasEvents) {
                    const events = jsonData.eventsInfo
                    events.forEach((event) => {
                        // render event icon
                        // set div id to be the event id
                        currentCell.append('<div class="event" id="event'+event[0]+'" data-event-id="'+event[0]+'"></div>');
                        $('#event'+event[0]).html(event[1] + ', ' + event[2]);
                        // set event listener for deleting and editing
                        $('#event'+event[0]).on('click', eventDetails);
                        // set color of event based on importance
                        if(event[3] == 'h') {
                            $('#event'+event[0]).css({'background-color':'red'});
                        } else if(event[3] == 'm') {
                            $('#event'+event[0]).css({'background-color':'orange'});
                        } else if(event[3] == 'l') {
                            $('#event'+event[0]).css({'background-color':'yellow'});
                        }
                    })
                }
            })
    
    })
    
}

// render pop up that will show event details
function eventDetails(event) {
    currentEvent = event.target
    // get event ID
    let eventID = currentEvent.getAttribute("data-event-id");

    // create data to be sent
    const data = {token : csrfToken, event : eventID};

    // send data
    // different php file because now we have event id whereas when we are rendering the events we do not have the id
    fetch('get-event-details.php',{
        method: 'POST',  // method to send data
        headers: {
            'Content-Type': 'application/json' // specify that this will be a json data type
        },
        body: JSON.stringify(data) // turn recevied data into json format
        })
        .then(res => res.json())
        .then(function(jsonData) { 
            if(jsonData.success) {
                // add information to event details box
                $('#title').html(jsonData.eventDetails[0]);
                $('#time').html(jsonData.eventDetails[1] + ' ' + jsonData.eventDetails[2]);
                $('#descript').html(jsonData.eventDetails[3]);
                $('#importance').html(jsonData.eventDetails[4]);
                if(jsonData.eventDetails[4] == 'h') {
                    $('#importance').html('!!! high importance !!!');
                } else if(jsonData.eventDetails[4] == 'm') {
                    $('#importance').html('!! medium importance !!');
                } else if(jsonData.eventDetails[4] == 'l') {
                    $('#importance').html('! low importance !');
                }
                
                // show event details box
                $('#pop-up').css({'display':'block'});
                clearPopUpDisplay();
                $('#event-details').css({'display':'block'});
            } else {
                alert('Failed to retrieve event details.');
                console.log(jsonData.error);
            }
        })
    
    // check if the event is shared
    // create data to be sent
    const dataShare = {token : csrfToken, event : eventID};
    fetch('is-shared.php',{
        method: 'POST',  // method to send data
        headers: {
            'Content-Type': 'application/json' // specify that this will be a json data type
        },
        body: JSON.stringify(data) // turn recevied data into json format
        })
        .then(res => res.json())
        .then(function(jsonData) { 
            if(jsonData.success) {
                console.log(jsonData.shared);
                if(jsonData.shared) {
                    $('#shared').html('Shared event');
                } else {
                    $('#shared').html('Private event');
                }
            } else {
                alert('Failed to retrieve event share status');
                console.log(jsonData.error);
            }
        })
    
}

// functions related to the event details page
function eventDelete() {
    // get event ID
    let eventID = currentEvent.getAttribute("data-event-id");
    
    // create data to be sent
    const data = {token : csrfToken, inputEventID : eventID};

    fetch('delete-event.php',{
        method: 'POST',  // method to send data
        headers: {
            'Content-Type': 'application/json' // specify that this will be a json data type
        },
        body: JSON.stringify(data) // turn recevied data into json format
        })
        .then(res => res.json())
        .then(function(jsonData) { 
            if(jsonData.success) {
                closeEvent();
                renderCalendar();
            } else {
                alert('Failed to delete.');
                console.log(jsonData.error);
            }
        })

    
}

function eventEdit() {
    // get event ID
    let eventID = currentEvent.getAttribute("data-event-id");

    // create data to be sent
    let data = {token : csrfToken, event : eventID};

    // get event details back from the server and disply in edit boxes
    fetch('get-event-details.php',{
        method: 'POST',  // method to send data
        headers: {
            'Content-Type': 'application/json' // specify that this will be a json data type
        },
        body: JSON.stringify(data) // turn recevied data into json format
        })
        .then(res => res.json())
        .then(function(jsonData) { 
            if(jsonData.success) {
                // put info to be edited into event edit boxes
                $('#edit-event-title').val(jsonData.eventDetails[0]);
                $('#edit-event-date').val(jsonData.eventDetails[1]);
                $('#edit-event-time').val(jsonData.eventDetails[2]);
                $('#edit-event-descript').val(jsonData.eventDetails[3]);
                // display current importance of event
                document.getElementById('edit-import-high').checked = false;
                document.getElementById('edit-import-med').checked = false;
                document.getElementById('edit-import-low').checked = false;
                if(jsonData.eventDetails[4] == 'h') {
                    document.getElementById('edit-import-high').checked = true;
                } else if(jsonData.eventDetails[4] == 'm') {
                    document.getElementById('edit-import-med').checked = true;
                } else if(jsonData.eventDetails[4] == 'l') {
                    document.getElementById('edit-import-low').checked = true;
                }
                // display event edit box
                clearPopUpDisplay();
                $('#event-editor').css({'display':'block'});
            } else {
                alert('Failed to retrieve event details.');
                console.log(jsonData.error);
            }
        })
    
}

function eventUpdate() {
    // get event ID
    let eventID = currentEvent.getAttribute("data-event-id");
    // get data from edit input boxes
    const eventTitle = $('#edit-event-title').val();
    const eventDate = $("#edit-event-date").val();
    let eventTime = $('#edit-event-time').val();
    const eventDescript = $("#edit-event-descript").val();
    let inputImport;
    if(document.getElementById('edit-import-high').checked == true) {
        inputImport = 'h';
    } else if (document.getElementById('edit-import-med').checked == true) {
        inputImport = 'm';
    } else if (document.getElementById('edit-import-low').checked == true) {
        inputImport = 'l';
    }
    
    // make sure none of input fields are empty, will be checked again on the server side
    if(/^$/.test(eventTitle) || /^$/.test(eventDate) || /^$/.test(eventTime)) { 
        // one of the required fields is empty
        alert('Please make sure you have entered a title, date, and time.');
    } else {
        // create data to be sent
        let data;
        if(inputImport) {
            data = {token : csrfToken, event : eventID, title : eventTitle, date : eventDate, time : eventTime, descript : eventDescript, import : inputImport};
        } else {
            data = {token : csrfToken, user : userID, title : eventTitle, date : eventDate, time : eventTime, descript : eventDescript};
        }   

        // send data
        fetch('update-event.php',{
        method: 'POST',  // method to send data
        headers: {
            'Content-Type': 'application/json' // specify that this will be a json data type
        },
        body: JSON.stringify(data) // turn data into json format
        })
        .then(res => res.json())
        .then(function(jsonData) { 
            if(jsonData.success) {
                closeEvent();
                renderCalendar();
            } else {
                alert('Failed to update event.');
                console.log(jsonData.error);
            }
        })
    }
}

function eventShare() {
    // get input using jquery
    const shareUser = $('#share-user').val();
    // get event ID
    let eventID = currentEvent.getAttribute("data-event-id");

    // make sure input field is not empty, will be checked again on server side
    if(/^$/.test(shareUser)) {
        alert('Please make sure you have entered a username.');
    } else {
        // input field is not empty, retrieve user id for the given username
        // create data set containing username to be sent
        const data = {token : csrfToken, user : shareUser};
        
        // get the id of the user that the event will be shared with
        fetch('get-user-id.php',{
        method: 'POST',  // method to send data
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(function(jsonData) { 
            if(jsonData.success) {
                // successfully got id of the user to be shared with
                let shareUserID = jsonData.shareUserID;

                // pair user id with event id
                // make sure event id is not empty and an int, will be checked again on the server side
                if(/^\d+$/.test(eventID)) {
                    // input field is not empty, add event user pair to the permissions table
                    // create data set containing user id and event id
                    const data = {token : csrfToken, user : shareUserID, event : eventID};
  
                    fetch('event-user-pair.php',{
                    method: 'POST',  // method to send data
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                    })
                    .then(res => res.json())
                    .then(function(jsonData) { 
                        console.log(jsonData.success);
                        if(jsonData.success) {
                            alert('Event successfully shared with '+shareUser+'!');
                            closeEvent();
                        } else {
                            alert('Failed to share event');
                            console.log(jsonData.error);
                        }
                    })
                } 
            } else {
                alert('Failed to get user id. Check that username is correct.');
                console.log(jsonData.error);
            }
        })
    }
}

function setCalendarButtons() {
    $('#prev').on('click', prevMonth);
    $('#next').on('click', nextMonth);
}

// functions related to switching calendar to a different month

function prevMonth () {
    // update month object
    monthObj = monthObj.prevMonth();
    update(monthObj);
}

function nextMonth() {
    // update month object
    monthObj = monthObj.nextMonth();
    update(monthObj);
}

function update(monthObj) {
    console.log(monthObj.year);
    console.log(monthObj.month);
    // update date object while keeping the same day of the month
    date = monthObj.getDateObject(date.getDate());
    // render calendar for the new month
    renderCalendar();
}