var a = {};
var s = {};
var app = require('http').createServer(handler),
        io = require('socket.io').listen(app),
        fs = require('fs');

// creating the server ( localhost:8000 )
app.listen(8000);

console.log('server listening on localhost:8000');


function handler(req, res) {

}

io.sockets.on('connected', function (socket) {
    console.log("hello");
});

// creating a new websocket to keep the content updated without any AJAX request
io.sockets.on('connection', function (socket) {

    socket.on('store', function (data) {
     

//store sockets
        s[socket.id] = socket;

        if (a[data["userid"]]) {
            var k = 0;
            a[data["userid"]].forEach(function (item) {
                if (!s[item])
                    a[data["userid"]].splice(k, 1);

            })

            a[data["userid"]].push(socket.id);
        }
        else
        {
            a[data["userid"]] = [];
            a[data["userid"]].push(socket.id);
        }
       
    });

    socket.on('send', function (data) {
      
        var nam = data["name"]

        var not = 'yo'
        if (a[data["to"]])
        {

            a[data["to"]].forEach(function (item) {
                if (s[item])
                    s[item].emit("mes", not);

            })
        }

    });
    
     socket.on('notii', function (data) {
      
        var noti = data["noti"]

    
        if (a[data["to"]])
        {

            a[data["to"]].forEach(function (item) {
                if (s[item])
                    s[item].emit("notiii", noti);

            })
        }

    });
    
    
    socket.on('disconnect', function () {
        delete s[socket.id];
    });



});