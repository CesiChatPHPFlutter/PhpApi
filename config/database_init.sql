DROP TABLE Messages;
DROP TABLE Users;

CREATE TABLE Users (
                       user_id INTEGER PRIMARY KEY AUTO_INCREMENT,
                       name VARCHAR(255),
                       mail VARCHAR(255),
                       password VARCHAR(255)
);

CREATE TABLE Messages (
                          message_id INTEGER PRIMARY KEY AUTO_INCREMENT,
                          sender_id INTEGER,
                          receiver_id INTEGER,
                          content TEXT,
                          timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                          FOREIGN KEY (sender_id) REFERENCES Users(user_id),
                          FOREIGN KEY (receiver_id) REFERENCES Users(user_id)
);