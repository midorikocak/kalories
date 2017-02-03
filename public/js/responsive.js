(function( window, undefined ) {
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
        if (request.readyState === 4) {
            if (request.status === 200) {
                responseData = JSON.parse(request.responseText);
                if (responseData.username == null) {
                    logout();
                }
                else{
                    loadLayout();
                }
            } else {
                mainContentElement.innerHTML = 'An error occurred during your request: ' + request.status + ' ' + request.statusText;
            }
        }
    };
    request.open('GET', "/kalories/app.php/user");
    request.send();
})(window);

var bodyElement = document.querySelector('body')

var loadLayout = function () {
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
        if (request.readyState === 4) {
            if (request.status === 200) {
                bodyElement.innerHTML = request.responseText;
                initApplication();
            } else {
                bodyElement.innerHTML = 'An error occurred during your request: ' + request.status + ' ' + request.statusText;
            }
        }
    };
    request.open('GET', "/kalories/public/layout.html");
    request.send();
};

var logout = function () {
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
        if (request.readyState === 4) {
            if (request.status === 200) {
                window.location.href = "/kalories/public/login.html";
            } else {
                mainContentElement.innerHTML = 'An error occurred during your request: ' + request.status + ' ' + request.statusText;
            }
        }
    };
    request.open('GET', "/kalories/app.php/users/logout");
    request.send();
};

var initApplication = function () {
    var mainContentElement = document.querySelector('.content');
    var homeButton = document.querySelector('#home');
    var settingsButton = document.querySelector('#settings');
    var logoutButton = document.querySelector('#logout');
    var lastWeekButton = document.querySelector('#last-week');
    var monthButton = document.querySelector('#calendar');
    var todayButton = document.querySelector('#today');
    var mealsWithControls = document.createElement('meals-withcontrols');

    var todaysDate = new Date();
    var isoDate = todaysDate.toISOString()
    isoDate = isoDate.slice(0, isoDate.indexOf('T'))

    monthButton.addEventListener('click', function () {
        month();
    });

    todayButton.addEventListener('click', function () {
        dailyMeals(isoDate);
    });

    homeButton.addEventListener('click', function () {
        home();
    });

    lastWeekButton.addEventListener('click', function () {
        lastWeek();
    });

    settingsButton.addEventListener('click', function () {
        settings();
    });

    logoutButton.addEventListener('click', function () {
        logout();
    });

    var dailyMeals = function (date) {
        mainContentElement.innerHTML = '';
        mealsWithControls.setDate(date);
        mainContentElement.appendChild(mealsWithControls);
    }

    var home = function () {
        mainContentElement.innerHTML = '';
        if (mealsWithControls.url != '/kalories/app.php/meals')
            mealsWithControls.url = '/kalories/app.php/meals';
        mealsWithControls.setDate('');
        mainContentElement.appendChild(mealsWithControls);
    };

    var lastWeek = function () {
        mainContentElement.innerHTML = '';
        mealsWithControls.url = '/kalories/app.php/meals/last-week';
        mealsWithControls.removeAttribute('date');
        mealsWithControls.setDate('');
        mealsWithControls.table.reload();
        mainContentElement.appendChild(mealsWithControls);
    };

    var settings = function () {
        var request = new XMLHttpRequest();

        request.onreadystatechange = function () {
            if (request.readyState === 4) {
                if (request.status === 200) {
                    mainContentElement.innerHTML = request.responseText;
                    var arr = mainContentElement.getElementsByTagName('script')
                    for (var n = 0; n < arr.length; n++)
                        eval(arr[n].innerHTML)//run script inside div
                } else {
                    mainContentElement.innerHTML = 'An error occurred during your request: ' + request.status + ' ' + request.statusText;
                }
            }
        };
        request.open('GET', "/kalories/public/settings.html");
        request.send();
    };

    var month = function () {
        var request = new XMLHttpRequest();

        request.onreadystatechange = function () {
            if (request.readyState === 4) {
                if (request.status === 200) {
                    mainContentElement.innerHTML = request.responseText;
                    var arr = mainContentElement.getElementsByTagName('script')
                    for (var n = 0; n < arr.length; n++)
                        eval(arr[n].innerHTML)//run script inside div
                } else {
                    mainContentElement.innerHTML = 'An error occurred during your request: ' + request.status + ' ' + request.statusText;
                }
            }
        };
        request.open('GET', "/kalories/public/month.html");
        request.send();
    };
};

