# Requirements Document

## Introduction

Sistem Absensi Real-Time Terdistribusi adalah platform absensi berbasis web yang dirancang untuk mengatasi masalah sistem absensi konvensional yang hanya menggunakan satu server. Sistem ini memungkinkan data absensi dari berbagai lokasi/cabang untuk tersinkronisasi secara real-time di semua server tanpa perlu refresh halaman. Dengan arsitektur terdistribusi, sistem tetap dapat beroperasi meskipun salah satu server mengalami gangguan (high availability).

## Glossary

- **System**: Sistem Absensi Real-Time Terdistribusi
- **Node**: Instance Laravel application yang berjalan di container Docker
- **Load Balancer**: Nginx yang mendistribusikan traffic ke multiple nodes
- **WebSocket Server**: Laravel Reverb server untuk komunikasi real-time
- **Message Broker**: Redis yang menangani pub/sub dan queue
- **Admin**: User dengan role administrator yang dapat melihat semua data
- **Karyawan**: User dengan role karyawan yang hanya dapat melihat data pribadi
- **Absensi Masuk**: Event ketika karyawan mencatat waktu kedatangan
- **Absensi Pulang**: Event ketika karyawan mencatat waktu kepulangan
- **Geolocation**: Koordinat geografis (latitude, longitude) dari lokasi absensi
- **Real-time Sync**: Sinkronisasi data otomatis tanpa refresh halaman
- **Event Broadcasting**: Mekanisme untuk mengirim event ke semua connected clients
- **Eventual Consistency**: Model konsistensi dimana data akan konsisten setelah delay maksimal 1-2 detik

## Requirements

### Requirement 1: Authentication & Authorization

**User Story:** Sebagai pengguna sistem, saya ingin dapat login dengan kredensial saya sehingga saya dapat mengakses fitur sesuai dengan role saya.

#### Acceptance Criteria

1. WHEN a user submits valid email and password, THE System SHALL authenticate the user and redirect to appropriate dashboard based on role
2. WHEN a user with admin role accesses the system, THE System SHALL grant access to admin dashboard with all data visibility
3. WHEN a user with karyawan role accesses the system, THE System SHALL grant access to portal karyawan with personal data visibility only
4. WHEN an unauthenticated user attempts to access protected routes, THE System SHALL redirect to login page
5. WHEN a user logs out, THE System SHALL terminate the session and redirect to login page

### Requirement 2: Absensi Masuk (Clock In)

**User Story:** Sebagai karyawan, saya ingin dapat mencatat waktu kedatangan saya sehingga kehadiran saya tercatat di sistem.

#### Acceptance Criteria

1. WHEN a karyawan clicks the "Absen Masuk" button, THE System SHALL capture current timestamp, geolocation coordinates, and node identifier
2. WHEN the absensi masuk data is captured, THE System SHALL save the record to the shared database with user_id, type as "in", timestamp, latitude, longitude, and node_id
3. WHEN the absensi masuk is saved successfully, THE System SHALL broadcast an event to all nodes via Redis pub/sub
4. WHEN a karyawan has already performed absensi masuk for the current day, THE System SHALL prevent duplicate absensi masuk and display an error message
5. WHEN absensi masuk is completed, THE System SHALL disable the "Absen Masuk" button and enable the "Absen Pulang" button

### Requirement 3: Absensi Pulang (Clock Out)

**User Story:** Sebagai karyawan, saya ingin dapat mencatat waktu kepulangan saya sehingga durasi kerja saya dapat dihitung.

#### Acceptance Criteria

1. WHEN a karyawan clicks the "Absen Pulang" button, THE System SHALL capture current timestamp and geolocation coordinates
2. WHEN the absensi pulang data is captured, THE System SHALL update the existing absensi record for the current day with jam_pulang timestamp
3. WHEN the absensi pulang is saved, THE System SHALL calculate work duration between jam_masuk and jam_pulang
4. WHEN the absensi pulang is completed, THE System SHALL broadcast an event to all nodes via Redis pub/sub
5. IF a karyawan has not performed absensi masuk, THEN THE System SHALL keep the "Absen Pulang" button disabled

### Requirement 4: Real-time Dashboard Updates

**User Story:** Sebagai admin, saya ingin melihat absensi terbaru secara real-time sehingga saya dapat memonitor kehadiran karyawan tanpa refresh halaman.

#### Acceptance Criteria

1. WHEN a new absensi event occurs on any node, THE System SHALL broadcast the event through WebSocket Server to all connected clients
2. WHEN an admin dashboard receives an absensi event, THE System SHALL update the "Absensi Terbaru" list without page refresh
3. WHEN the dashboard is updated, THE System SHALL display a "Live" indicator to show real-time status
4. WHEN the WebSocket connection is lost, THE System SHALL attempt to reconnect with exponential backoff strategy
5. WHILE the dashboard is active, THE System SHALL maintain WebSocket connection for receiving real-time updates

### Requirement 5: Multi-Node Load Balancing

**User Story:** Sebagai system administrator, saya ingin traffic didistribusikan ke multiple nodes sehingga sistem dapat menangani load yang lebih besar.

#### Acceptance Criteria

1. WHEN a user makes a request to the system, THE Load Balancer SHALL distribute the request to available nodes using round-robin algorithm
2. WHEN a node becomes unhealthy, THE Load Balancer SHALL automatically route traffic to healthy nodes only
3. WHEN all nodes share the same MySQL database, THE System SHALL ensure data consistency across nodes
4. WHEN a request is processed by any node, THE System SHALL record the node_id in the absensi record
5. THE System SHALL perform health checks on all nodes every 10 seconds

### Requirement 6: High Availability & Fault Tolerance

**User Story:** Sebagai pengguna sistem, saya ingin sistem tetap dapat digunakan meskipun ada server yang down sehingga proses absensi tidak terganggu.

#### Acceptance Criteria

1. WHEN one node fails, THE System SHALL continue operating using remaining healthy nodes
2. WHEN two nodes fail, THE System SHALL continue operating with degraded performance using the remaining node
3. IF Redis service fails, THEN THE System SHALL continue accepting absensi in degraded mode without real-time updates
4. IF MySQL database fails, THEN THE System SHALL reject new absensi requests and display error message
5. WHEN WebSocket Server fails, THE System SHALL allow absensi operations but require manual page refresh for updates

### Requirement 7: Admin Dashboard

**User Story:** Sebagai admin, saya ingin melihat overview sistem dan statistik absensi sehingga saya dapat memonitor performa sistem.

#### Acceptance Criteria

1. WHEN an admin accesses the dashboard, THE System SHALL display metric cards showing absensi hari ini, minggu ini, tingkat kehadiran, and server online count
2. WHEN the dashboard loads, THE System SHALL display the list of latest absensi with real-time updates
3. WHEN the dashboard loads, THE System SHALL display server status panel showing all nodes with their online/offline status
4. WHEN a server status changes, THE System SHALL update the status indicator and last sync timestamp
5. THE System SHALL update all dashboard metrics in real-time as new absensi events occur

### Requirement 8: Riwayat Absensi (Admin)

**User Story:** Sebagai admin, saya ingin melihat riwayat absensi semua karyawan sehingga saya dapat melakukan audit dan reporting.

#### Acceptance Criteria

1. WHEN an admin accesses the riwayat page, THE System SHALL display all absensi records from all karyawan
2. WHEN an admin enters a name in the search box, THE System SHALL filter the absensi records by karyawan name
3. WHEN an admin selects a date range, THE System SHALL filter the absensi records by the selected date range
4. WHEN an admin clicks the export button, THE System SHALL generate and download a CSV or Excel file containing filtered absensi data
5. THE System SHALL display absensi records with columns: tanggal, nama, jam_masuk, jam_pulang, durasi, and status

### Requirement 9: Riwayat Absensi (Karyawan)

**User Story:** Sebagai karyawan, saya ingin melihat riwayat absensi saya sendiri sehingga saya dapat memonitor kehadiran saya.

#### Acceptance Criteria

1. WHEN a karyawan accesses the riwayat page, THE System SHALL display only absensi records belonging to the authenticated user
2. WHEN a karyawan selects a date range, THE System SHALL filter personal absensi records by the selected date range
3. WHEN a karyawan clicks the export button, THE System SHALL generate and download personal absensi data only
4. THE System SHALL prevent karyawan from viewing other users' absensi data
5. WHEN no absensi records exist, THE System SHALL display "Tidak ada data riwayat ditemukan" message

### Requirement 10: Data Synchronization

**User Story:** Sebagai system architect, saya ingin data absensi tersinkronisasi di semua nodes sehingga eventual consistency tercapai.

#### Acceptance Criteria

1. WHEN an absensi event is created on any node, THE System SHALL publish the event to Redis pub/sub channel
2. WHEN a node receives an event from Redis pub/sub, THE System SHALL broadcast the event to connected WebSocket clients
3. WHEN data synchronization occurs, THE System SHALL achieve eventual consistency within maximum 2 seconds
4. WHEN a conflict occurs (duplicate absensi), THE System SHALL apply First-Write-Wins strategy based on timestamp
5. THE System SHALL log all absensi events to attendance_logs table for audit trail

### Requirement 11: Geolocation Tracking

**User Story:** Sebagai admin, saya ingin mengetahui lokasi karyawan saat absen sehingga saya dapat memverifikasi kehadiran fisik.

#### Acceptance Criteria

1. WHEN a karyawan performs absensi, THE System SHALL request browser geolocation permission
2. WHEN geolocation permission is granted, THE System SHALL capture latitude and longitude coordinates
3. WHEN geolocation data is captured, THE System SHALL save the coordinates along with absensi record
4. IF geolocation permission is denied, THEN THE System SHALL still allow absensi but save null values for coordinates
5. WHEN admin views absensi records, THE System SHALL display location information if available

### Requirement 12: Status Calculation

**User Story:** Sebagai admin, saya ingin sistem menghitung status kehadiran karyawan sehingga saya dapat mengidentifikasi keterlambatan atau ketidakhadiran.

#### Acceptance Criteria

1. WHEN a karyawan performs absensi masuk before 08:30, THE System SHALL mark status as "Hadir"
2. WHEN a karyawan performs absensi masuk after 08:30, THE System SHALL mark status as "Terlambat"
3. WHEN a karyawan performs absensi masuk but not absensi pulang by end of day, THE System SHALL mark status as "Alpha"
4. WHEN work duration is calculated, THE System SHALL compute the difference between jam_masuk and jam_pulang in hours and minutes format
5. THE System SHALL display status with appropriate color coding (green for Hadir, yellow for Terlambat, red for Alpha)

### Requirement 13: Tentang Sistem Page

**User Story:** Sebagai pengguna sistem, saya ingin memahami teknologi dan tujuan sistem sehingga saya dapat mengetahui cara kerja sistem terdistribusi.

#### Acceptance Criteria

1. WHEN a user accesses the Tentang Sistem page, THE System SHALL display system title and description
2. WHEN the page loads, THE System SHALL display a card explaining project objectives including high availability and data consistency
3. WHEN the page loads, THE System SHALL display technology stack cards with icons for Laravel, Redis, MySQL, Docker, and Nginx
4. THE System SHALL provide clear explanation of distributed system concepts implemented
5. THE System SHALL be accessible by both admin and karyawan roles

### Requirement 14: Docker Container Orchestration

**User Story:** Sebagai DevOps engineer, saya ingin sistem berjalan di multiple containers sehingga saya dapat mendemonstrasikan arsitektur terdistribusi.

#### Acceptance Criteria

1. THE System SHALL run three Laravel application nodes (app-node-1, app-node-2, app-node-3) in separate Docker containers
2. THE System SHALL run one Laravel Reverb WebSocket server in a separate Docker container
3. THE System SHALL run one Redis instance in a Docker container for pub/sub and caching
4. THE System SHALL run one MySQL database instance in a Docker container
5. THE System SHALL run one Nginx load balancer in a Docker container to distribute traffic to application nodes

### Requirement 15: Environment Configuration

**User Story:** Sebagai developer, saya ingin sistem dapat berjalan di environment development dan production sehingga saya dapat develop dan deploy dengan mudah.

#### Acceptance Criteria

1. WHEN running in development mode, THE System SHALL use SQLite database for zero configuration setup
2. WHEN running in Docker production mode, THE System SHALL use MySQL database for reliability
3. WHEN environment variables are configured, THE System SHALL read configuration from .env file
4. THE System SHALL support separate configuration for each node while sharing the same database
5. WHEN Docker Compose is executed, THE System SHALL automatically setup all required services and dependencies
