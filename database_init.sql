CREATE TABLE Users (
                       user_id INTEGER PRIMARY KEY AUTO_INCREMENT,
                       name VARCHAR(255),
                       password VARCHAR(255)
);

CREATE TABLE Messages (
                          message_id INTEGER PRIMARY KEY AUTO_INCREMENT,
                          sender_id INTEGER,
                          receiver_id INTEGER,
                          content TEXT,
                          FOREIGN KEY (sender_id) REFERENCES Users(user_id),
                          FOREIGN KEY (receiver_id) REFERENCES Users(user_id)
);

-- Inserting users
INSERT INTO Users (name, password) VALUES ('User1', 'password1');
INSERT INTO Users (name, password) VALUES ('User2', 'password2');
INSERT INTO Users (name, password) VALUES ('User3', 'password3');

-- Inserting messages
INSERT INTO Messages (sender_id, receiver_id, content) VALUES
                                                           (1, 2, 'Hello, User2!'),
                                                           (2, 1, 'Hi, User1! How are you?'),
                                                           (1, 3, 'Hey, User3!'),
                                                           (3, 1, 'Hello, User1!'),
                                                           (2, 3, 'Hi, User3. What are you up to?'),
                                                           (3, 2, 'Not much, just relaxing.');

