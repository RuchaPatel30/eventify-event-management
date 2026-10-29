
CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(80) NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    venue VARCHAR(180) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    seats INT NOT NULL DEFAULT 50,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    attendee_name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    tickets INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_booking_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

INSERT INTO events (title, category, event_date, event_time, venue, price, seats, description) VALUES
('Tech Innovation Summit', 'Technology', '2027-01-15', '10:00:00', 'City Convention Centre', 499.00, 120, 'Explore new ideas, web technologies and digital innovation with students and professionals.'),
('Creative Art Workshop', 'Workshop', '2027-02-08', '11:30:00', 'Studio 21, Main Hall', 199.00, 40, 'A friendly hands-on workshop for creative minds. Materials are included.'),
('Campus Music Night', 'Entertainment', '2027-03-12', '18:00:00', 'Open Air Auditorium', 299.00, 200, 'Enjoy live performances, music and a memorable evening with friends.'),
('Startup Networking Meet', 'Business', '2027-04-05', '09:30:00', 'Innovation Hub', 0.00, 80, 'Meet aspiring founders, share ideas and build meaningful connections.')
ON DUPLICATE KEY UPDATE title = VALUES(title);
