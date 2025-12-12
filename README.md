# 🎮 GameBridge - Advanced Games Management System

A comprehensive PHP-based web application for managing games, categories, and reviews with advanced features including trash management, bulk operations, and export functionality.

## ✨ Features

### Core Features
- **Game Management**: Create, read, update, and delete games
- **Category Management**: Organize games into categories
- **Review System**: Users can rate and review games
- **User Authentication**: Developer accounts for game management
- **File Upload**: Support for game images and game files (ZIP, RAR, EXE, APK)

### Advanced Features

#### 🗑️ Trash Management System
- **Soft Delete**: Games are moved to trash instead of being permanently deleted
- **Trash Dashboard**: View all deleted games with deletion timestamps
- **Restore Functionality**: Restore deleted games back to active status
- **Permanent Delete**: Permanently delete games from trash (with file cleanup)
- **Empty Trash**: Bulk delete all games in trash at once
- **Trash Badge**: Visual indicator showing number of deleted games in dashboard

#### 🎯 Advanced Games Page Features
- **Grid/List View Toggle**: Switch between grid and list view modes
- **Advanced Search**: Real-time search with debouncing
- **Advanced Filters**:
  - Category filtering
  - Rating range (min/max)
  - Date range filtering
  - Multiple sort options (newest, oldest, A-Z, Z-A, highest/lowest rated)
- **Bulk Actions**:
  - Select multiple games with checkboxes
  - Bulk export selected games
  - Bulk delete selected games
- **Export Functionality**: Export games to CSV format
- **Pagination**: Navigate through large game lists
- **Statistics Bar**: Display total and filtered game counts
- **Real-time Filtering**: Instant results as you type

#### 📊 Dashboard Features
- **Statistics Overview**: Total games, categories, reviews, and average ratings
- **Quick Actions**: Easy access to common tasks
- **Trash Indicator**: Badge showing deleted games count
- **Recent Activity**: View latest games and reviews
- **Search & Filter**: Quick search and filter capabilities

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (for local development)

### Setup Instructions

1. **Clone or Download the Project**
   ```bash
   cd C:\xampp\htdocs\
   # Place the project folder here
   ```

2. **Database Configuration**
   - Open `config.php`
   - Update database credentials:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "";
     $dbname = "gamebridge";
     ```

3. **Create Database**
   ```sql
   CREATE DATABASE gamebridge;
   ```

4. **Database Schema**
   The application uses the following main tables:
   - `games` - Stores game information
   - `categories` - Game categories
   - `reviews` - User reviews and ratings
   - `users` - User accounts

   Make sure your `games` table has a `deleted_at` column for trash functionality:
   ```sql
   ALTER TABLE games ADD COLUMN deleted_at DATETIME NULL;
   ```

5. **Configure Base URL**
   - Open `config.php`
   - Update `BASE_URL` to match your project path:
     ```php
     define('BASE_URL', '/games_managment advanced');
     ```

6. **Set Permissions**
   - Ensure `uploads/` directory is writable
   - Set appropriate permissions for file uploads

7. **Start Server**
   - Start Apache and MySQL from XAMPP Control Panel
   - Access the application at: `http://localhost/games_managment advanced/`

## 📁 Project Structure

```
games_managment advanced/
├── assets/
│   ├── images/          # Logo and default images
│   ├── style.css        # Main stylesheet
│   └── index.html       # Home page
├── config.php           # Database configuration
├── index.php            # Main router
├── controller/
│   ├── GameController.php
│   ├── CategoryController.php
│   └── ReviewController.php
├── model/
│   ├── GameModel.php
│   ├── CategoryModel.php
│   └── ReviewModel.php
├── view/
│   ├── back office/
│   │   ├── dashboard.php
│   │   ├── games_dashboard.php
│   │   ├── trash.php          # Trash management page
│   │   └── game/
│   │       └── list.php
│   └── front office/
│       ├── game/
│       │   ├── list.php       # Advanced games listing
│       │   ├── create.php
│       │   ├── edit.php
│       │   ├── show.php
│       │   └── statistics.php
│       ├── category/
│       └── review/
└── uploads/
    └── images/          # Uploaded game images
```

## 🎮 Usage Guide

### Managing Games

#### Creating a Game
1. Navigate to Games page
2. Click "Upload New Game"
3. Fill in game details (title, description, category)
4. Upload game image (JPG, PNG, GIF - max 5MB)
5. Upload game file (ZIP, RAR, EXE, APK - max 500MB)
6. Click "Create Game"

#### Editing a Game
1. Go to Games page or Dashboard
2. Click "Edit" on the desired game
3. Update information and/or upload new files
4. Click "Update Game"

#### Deleting a Game
1. Click "Delete" on any game
2. Game is moved to trash (soft delete)
3. Access trash from dashboard to restore or permanently delete

### Trash Management

#### Accessing Trash
- Click "🗑️ Trash" link in dashboard navigation
- Or access directly: `index.php?controller=game&action=trash`

#### Restoring Games
1. Go to Trash page
2. Click "↺ Restore" on desired game
3. Game is restored to active status

#### Permanently Deleting
1. In Trash page, click "🗑️ Delete Forever"
2. Confirm the action
3. Game and all associated files are permanently removed

#### Emptying Trash
1. Click "🗑️ Empty Trash" button
2. Confirm the action
3. All games in trash are permanently deleted

### Advanced Games Page Features

#### View Modes
- **Grid View**: Card-based layout (default)
- **List View**: Detailed list with more information
- Toggle between views using the view buttons

#### Search & Filter
- **Basic Search**: Type in search box for real-time results
- **Category Filter**: Select category from dropdown
- **Sort Options**: Choose sorting method
- **Advanced Filters**: Click "⚙️ Advanced" to show:
  - Minimum/Maximum rating
  - Date range (from/to)

#### Bulk Operations
1. Check boxes next to games you want to select
2. Choose bulk action from dropdown:
   - Export Selected
   - Delete Selected
3. Click "Apply"

#### Export Games
- **Individual Export**: Click "📥 Export Games" button
- **Bulk Export**: Select games and use bulk action
- Exports to CSV format with all game data

## 🔧 Configuration

### File Upload Limits
Edit `php.ini` to adjust upload limits:
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
```

### Database Connection
Edit `config.php`:
```php
$servername = "localhost";
$username = "your_username";
$password = "your_password";
$dbname = "gamebridge";
```

## 🛠️ Technical Details

### Technologies Used
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Architecture**: MVC (Model-View-Controller)

### Key Classes

#### GameModel
- `getAllGames()` - Get all active games
- `getDeletedGames()` - Get all deleted games
- `deleteGame()` - Soft delete (move to trash)
- `restoreGame()` - Restore from trash
- `permanentlyDeleteGame()` - Hard delete
- `getDeletedGamesCount()` - Count deleted games
- `searchGames()` - Search functionality
- `filterGames()` - Filter by category/rating
- `sortGames()` - Sort games

#### GameController
- `index()` - List all games
- `create()` - Show create form
- `store()` - Save new game
- `edit()` - Show edit form
- `update()` - Update game
- `delete()` - Soft delete
- `trash()` - Show trash page
- `restore()` - Restore game
- `permanentDelete()` - Permanently delete
- `emptyTrash()` - Clear all trash
- `export()` - Export to CSV
- `search()` - Search games
- `filter()` - Filter games
- `sort()` - Sort games

## 📝 API Routes

### Game Routes
```
index.php?controller=game&action=index          # List games
index.php?controller=game&action=create         # Create form
index.php?controller=game&action=store          # Save game
index.php?controller=game&action=edit&id=X      # Edit form
index.php?controller=game&action=update         # Update game
index.php?controller=game&action=delete&id=X    # Delete game
index.php?controller=game&action=trash          # Trash page
index.php?controller=game&action=restore&id=X   # Restore game
index.php?controller=game&action=permanentDelete&id=X  # Permanent delete
index.php?controller=game&action=emptyTrash     # Empty trash
index.php?controller=game&action=export        # Export CSV
index.php?controller=game&action=search&q=term  # Search
index.php?controller=game&action=filter&category_id=X  # Filter
index.php?controller=game&action=sort&sort_by=X&order=Y  # Sort
index.php?controller=game&action=dashboard      # Dashboard
```

## 🎨 Customization

### Styling
- Main stylesheet: `assets/style.css`
- Uses CSS variables for theming:
  - `--bg-dark`: Dark background
  - `--bg-card`: Card background
  - `--accent`: Accent color (green)
  - `--text`: Text color

### Adding Features
The MVC architecture makes it easy to extend:
1. Add methods to Model classes
2. Add actions to Controller classes
3. Create views in appropriate directories

## 🔒 Security Considerations

- File upload validation (type and size)
- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)
- Session management
- File path sanitization

## 🐛 Troubleshooting

### Common Issues

**Games not appearing:**
- Check database connection in `config.php`
- Verify `deleted_at` column exists in games table
- Check if games have `deleted_at` set to NULL

**File upload fails:**
- Check PHP upload limits in `php.ini`
- Verify `uploads/` directory permissions
- Check file size and type restrictions

**Trash not working:**
- Ensure `deleted_at` column exists
- Check database permissions
- Verify controller routes

## 📄 License

This project is provided as-is for educational and development purposes.

## 👥 Support

For issues or questions:
1. Check this README
2. Review code comments
3. Check database configuration
4. Verify file permissions

## 🚀 Future Enhancements

Potential features to add:
- User authentication system
- Role-based access control
- Image optimization
- Advanced analytics
- API endpoints
- Multi-language support
- Email notifications
- Game versioning
- Backup/restore functionality

---

**Version**: 2.0  
**Last Updated**: 2025  
**Author**: GameBridge Development Team

