const express = require("express");
const http = require("http");
const { Server } = require("socket.io");

const app = express();
const server = http.createServer(app);
const io = new Server(server);

const rooms = {};
const socketToUserMap = {};

app.use(express.static("public"));

io.on("connection", (socket) => {
    console.log("New user connected:", socket.id);

    socket.on("register-user", (userData) => {
        let userID, firstname;
        
        if (typeof userData === 'object' && userData !== null) {
            userID = userData.userID;
            firstname = userData.firstname || 'Anonymous';
        } else {
            userID = userData;
            firstname = 'Anonymous';
        }
        
        if (!userID) {
            console.error(`Socket ${socket.id} tried to register without a valid user ID`);
            return;
        }
        
        console.log(`Mapping socket ${socket.id} to user ${userID} (${firstname})`);
        
        socketToUserMap[socket.id] = {
            userID,
            firstname
        };
    });

    socket.on("join-room", (roomId) => {
        const userData = socketToUserMap[socket.id];
        
        if (!userData || !userData.userID) {
            console.error(`Socket ${socket.id} tried to join room without registering a user ID`);
            return;
        }
        
        if (socket.roomId) {
            socket.leave(socket.roomId);
            if (rooms[socket.roomId]) {
                // Remove user from previous room
                rooms[socket.roomId] = rooms[socket.roomId].filter(user => user.userID !== userData.userID);
                
                // Notify others that user left using complete user data
                socket.to(socket.roomId).emit("user-left", userData);
            }
        }

        socket.join(roomId);
        socket.roomId = roomId;
        console.log(`User ${userData.firstname} (ID: ${userData.userID}, socket: ${socket.id}) joined room ${roomId}`);

        if (!rooms[roomId]) {
            rooms[roomId] = [];
        }

        // Ensure the new user isn't already in the room (in case of reconnection)
        const existingUserIndex = rooms[roomId].findIndex(user => user.userID === userData.userID);
        if (existingUserIndex >= 0) {
            rooms[roomId][existingUserIndex] = userData; // Update existing user data
        } else {
            // Add the new user to the room
            rooms[roomId].push(userData);
        }

        // Send existing users' data to new user (excluding themself)
        const otherUsers = rooms[roomId].filter(user => user.userID !== userData.userID);
        socket.emit("room-users", otherUsers);
        
        // Notify others that a new user joined with complete user data
        socket.to(roomId).emit("user-joined", userData);
    });

    socket.on("offer", (data) => {
        // Find the socket ID for the target user ID
        const targetSocketId = findSocketIdByUserId(data.to);
        if (targetSocketId) {
            // Get the sender's user data
            const senderData = socketToUserMap[socket.id];
            
            // Send offer with the sender's complete user data
            socket.to(targetSocketId).emit("offer", { 
                offer: data.offer, 
                from: senderData.userID,
                firstname: senderData.firstname
            });
        } else {
            console.error(`Cannot find socket for user ID: ${data.to}`);
        }
    });

    socket.on("answer", (data) => {
        const targetSocketId = findSocketIdByUserId(data.to);
        if (targetSocketId) {
            const senderData = socketToUserMap[socket.id];
            
            socket.to(targetSocketId).emit("answer", { 
                answer: data.answer, 
                from: senderData.userID,
                firstname: senderData.firstname
            });
        }
    });

    socket.on("ice-candidate", (data) => {
        const targetSocketId = findSocketIdByUserId(data.to);
        if (targetSocketId) {
            const senderData = socketToUserMap[socket.id];
            
            socket.to(targetSocketId).emit("ice-candidate", { 
                candidate: data.candidate, 
                from: senderData.userID,
                firstname: senderData.firstname
            });
        }
    });

    socket.on("disconnect", () => {
        const userData = socketToUserMap[socket.id];
        if (!userData) return;
        
        console.log(`User ${userData.firstname} (ID: ${userData.userID}, socket: ${socket.id}) disconnected`);
        
        // Remove user from all rooms
        for (const roomId in rooms) {
            if (rooms[roomId].some(user => user.userID === userData.userID)) {
                rooms[roomId] = rooms[roomId].filter(user => user.userID !== userData.userID);
                socket.to(roomId).emit("user-left", userData);
            }
        }
        
        // Remove from the map
        delete socketToUserMap[socket.id];
    });
});

// Updated helper function to find a socket ID by user ID
function findSocketIdByUserId(userId) {
    for (const socketId in socketToUserMap) {
        if (socketToUserMap[socketId].userID === userId) {
            return socketId;
        }
    }
    return null;
}

server.listen(3000, () => {
    console.log("WebRTC Server started at http://localhost:3000");
});