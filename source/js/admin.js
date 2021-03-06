$(document).ready(function () {

    // Run the init method on document ready:
    chat.init();

});

$('.saveUser').live('click', function (e) {
    var uID = getUserID(e);
    var status = getStatus(e);

    console.log($(this).serialize());

    $.chatPOST('saveUser', "uID=" + uID + "&status=" + status, function (r) {
        working = false;
        if (r.error) {
            chat.displayError(r.error);
        }
        else chat.displayError('saveUser');

    });


    return false;
});


$('.deleteUser').live('click', function (e) {
    var uID = getUserID(e);

    $.chatPOST('deleteUser', "uid=" + uID, function (r) {
        working = false;
        if (r.error) {
            chat.displayError(r.error);
        }
        else {
            $('table').find("tr[data-uid='" + uID + "']").fadeOut();
        }

        chat.displayError('deleteUser');


    });

});

function getUserID(e) {
    var tr = $(e.target).parent().parent();
    return tr[0].dataset.uid;
}

function getStatus(e) {
    var tr = $(e.target).parent().parent();
    return tr.find('td input').val();
}

var chat = {

    // data holds variables for use in the class:

    data: {
        lastID: 0,
        noActivity: 0
    },

    // Init binds event listeners and sets up timers:

    init: function () {

        // Using the defaultText jQuery plugin, included at the bottom:
        $('#name').defaultText('Nickname');
        $('#email').defaultText('Email (Gravatars are Enabled)');

        // Converting the #chatLineHolder div into a jScrollPane,
        // and saving the plugin's API in chat.data:


        // We use the working variable to prevent
        // multiple form submissions:

        var working = false;

        // Logging a person in the chat:

        $('#adminForm').submit(function () {


            var validName = validateInput($("#name"), "^([ \u00c0-\u01ffa-zA-Z'\-])+$");
            var validEmail = validateInput($("#email"), "^[-a-z0-9~!$%^&*_=+}{\'?]+(\.[-a-z0-9~!$%^&*_=+}{\'?]+)*@([a-z0-9_][-a-z0-9_]*(\.[-a-z0-9_]+)*\.(aero|ch|arpa|biz|com|coop|edu|gov|info|int|mil|museum|name|net|org|pro|travel|mobi|[a-z][a-z])|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,5})?$");

            if (!validName || !validEmail) return false;


            if (working) return false;
            working = true;

            // Using our chatPOST wrapper function
            // (defined in the bottom):

            console.log($(this).serialize());

            $.chatPOST('admin', $(this).serialize(), function (r) {
                working = false;

                if (r.error) {
                    chat.displayError(r.error);
                }
                else chat.displayError('admin');
            });

            $.chatPOST('administer', $(this).serialize(), function (r) {
                working = false;
                if (r.error) {
                    chat.displayError(r.error);
                }
                else chat.displayError('admin');

                console.log(r);

                $("#adminForm").hide(); //zum deaktivieren der Anzeige nach abschluss der arbeit

                var usersNoAdmin = r.filter(function (user) {
                    return (user ['status'] != 'admin');
                    chat.displayError("keinAdmin");

                });

                chat.displayError("angemeldet");


                usersNoAdmin.forEach(function (userRow) {
                    console.log(userRow);
                    $('#users').append("<tr data-uid='" + userRow['id'] + "'> <td>" + userRow['email'] + "</td> <td>" + userRow['name'] + "</td> <td> " + userRow['status'] + " </td>" +
                        "<td> <input value='" + userRow['status'] + "'> </td> " +
                        "<td>  <button class='blueButton saveUser'>save</button>" +
                        " <button class='blueButton deleteUser'>del</button></td></tr>");

                });


            });

            return false;

        });

        function validateInput(field, _regax) {
            da = field.val();
            var regex = new RegExp(_regax);
            if (!regex.test(da)) {
                alert("Bitte korr. Text eingeben");
                field.val("");
                return false;
            } else {
                return true;
            }
        }

        // Submitting a new chat entry:

        $('#submitForm').submit(function () {


            var esc_text = $('#chatText').val();
            text = safe_tags_replace(esc_text);

            if (text.length == 0) {
                return false;
            }

            if (working) return false;
            working = true;

            // Assigning a temporary ID to the chat:
            var tempID = 't' + Math.round(Math.random() * 1000000),
                params = {
                    id: tempID,
                    author: chat.data.name,
                    gravatar: chat.data.gravatar,
                    text: text.replace(/</g, '&lt;').replace(/>/g, '&gt;')
                };

            // Using our addChatLine method to add the chat
            // to the screen immediately, without waiting for
            // the AJAX request to complete:

            chat.addChatLine($.extend({}, params));

            // Using our chatPOST wrapper method to send the chat
            // via a POST AJAX request:

            $.chatPOST('submitChat', $(this).serialize(), function (r) {
                working = false;

                $('#chatText').val('');
                $('div.chat-' + tempID).remove();

                params['id'] = r.insertID;
                chat.addChatLine($.extend({}, params));
            });

            return false;
        });

        // Logging the user out:

        $('a.logoutButton').live('click', function () {

            $('#chatTopBar > span').fadeOut(function () {
                $(this).remove();
            });

            $('#submitForm').fadeOut(function () {
                $('#loginForm').fadeIn();
            });

            $.chatPOST('logout');

            return false;
        });

        // Checking whether the user is already logged (browser refresh)

        $.chatGET('checkLogged', function (r) {
            if (r.logged) {
                chat.login(r.loggedAs.name, r.loggedAs.gravatar);
            }
        });

        // Self executing timeout functions

        (function getChatsTimeoutFunction() {
            chat.getChats(getChatsTimeoutFunction);
        })();

        (function getUsersTimeoutFunction() {
            chat.getUsers(getUsersTimeoutFunction);
        })();

    },

    // The login method hides displays the
    // user's login data and shows the submit form

    login: function (name, gravatar) {

        chat.data.name = name;
        chat.data.gravatar = gravatar;
        $('#chatTopBar').html(chat.render('loginTopBar', chat.data));

        $('#loginForm').fadeOut(function () {
            $('#submitForm').fadeIn();
            $('#chatText').focus();
        });

    },

    // The render method generates the HTML markup
    // that is needed by the other methods:

    render: function (template, params) {

        var arr = [];
        switch (template) {
            case 'loginTopBar':
                arr = [
                    '<span><img src="', params.gravatar, '" width="23" height="23" />',
                    '<span class="name">', params.name,
                    '</span><a href="" class="logoutButton rounded">Logout</a></span>'];
                break;

            case 'chatLine':
                arr = [
                    '<div class="chat chat-', params.id, ' rounded"><span class="gravatar"><img src="', params.gravatar,
                    '" width="23" height="23" onload="this.style.visibility=\'visible\'" />', '</span><span class="author">', params.author,
                    ':</span><span class="text">', params.text, '</span><span class="time">', params.time, '</span></div>'];
                break;

            case 'user':
                arr = [
                    '<div class="user" title="', params.name, '"><img src="',
                    params.gravatar, '" width="30" height="30" onload="this.style.visibility=\'visible\'" /></div>'
                ];
                break;
        }

        // A single array join is faster than
        // multiple concatenations

        return arr.join('');

    },


    // Requesting a list with all users

    getUsers: function (callback) {
        $.chatGET('getUsers', function (r) {

            var users = [];

            for (var i = 0; i < r.users.length; i++) {
                if (r.users[i]) {
                    users.push(chat.render('user', r.users[i]));
                }
            }

            var message = '';

            if (r.total < 1) {
                message = 'No one is online';
            }
            else {
                message = r.total + ' ' + (r.total == 1 ? 'person' : 'people') + ' online';
            }

            users.push('<p class="count">' + message + '</p>');

            $('#chatUsers').html(users.join(''));

            setTimeout(callback, 15000);
        });
    },

    // This method displays an error message on the top of the page:

    displayError: function (msg) {
        var elem = $('<div>', {
            id: 'chatErrorMessage',
            html: msg
        });

        elem.click(function () {
            $(this).fadeOut(function () {
                $(this).remove();
            });
        });

        setTimeout(function () {
            elem.click();
        }, 5000);

        elem.hide().appendTo('body').slideDown();
    }
};

// Custom GET & POST wrappers:

$.chatPOST = function (action, data, callback) {
    $.post('php/ajax.php?action=' + action, data, callback, 'json');
}

$.chatGET = function (action, data, callback) {
    $.get('php/ajax.php?action=' + action, data, callback, 'json');
}

// A custom jQuery method for placeholder text:

$.fn.defaultText = function (value) {

    var element = this.eq(0);
    element.data('defaultText', value);

    element.focus(function () {
        if (element.val() == value) {
            element.val('').removeClass('defaultText');
        }
    }).blur(function () {
        if (element.val() == '' || element.val() == value) {
            element.addClass('defaultText').val(value);
        }
    });

    return element.blur();
}

var tagsToReplace = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;'
};

function replaceTag(tag) {
    return tagsToReplace[tag] || tag;
}

function safe_tags_replace(str) {
    return str.replace(/[&<>]/g, replaceTag);
}

