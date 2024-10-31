# Project: Google Sheets Integration App - Backend

**Objective**: Create an app that integrates seamlessly with Google Sheets. This app will allow users to view, add, edit, and delete data within a Google Sheet directly from the app. It will feature real-time syncing and a clean, user-friendly interface.

## Key Features
- **Google Sheets Integration**: Authenticate users via Google OAuth2 and interact with the Google Sheets API to perform CRUD operations.
- **Real-Time Syncing**: Any changes made in the Google Sheet or the app should be reflected in real-time.
- **User-Friendly Interface**: Clean, minimal UI designed with Tailwind CSS.
- **Single Page Application (SPA)**: Vue.js app to ensure a smooth, dynamic experience.

---

## Installation Process

### Prerequisites
- **Node.js** (v14 or higher)
- **Composer** (for Laravel installation)
- **Google Cloud Project** is configured with OAuth2 Client ID, Secret ID and Google Sheets API is enabled.

### Setting Up the Backend (Laravel)

1. **Unzip the folder google_sheet_task**:

3. **Configure Environment Variables**:
   - Copy the `.env.example` file to `.env`:
     ```bash
     cp .env.example .env
     ```
   - Update the `.env` file with the following Google API details:
     ```plaintext
     GOOGLE_CLIENT_ID=your-google-client-id
     GOOGLE_CLIENT_SECRET=your-google-client-secret
     GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
     ```
   - Set other environment variables such as database credentials.

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations** (if required):
   ```bash
   php artisan migrate
   ```

6. **Run the Backend Server**:
   ```bash
   php artisan serve
   ```
   By default, this will start the server on `http://localhost:8000`

---

## Usage Instructions

1. **Google Login**:
   - Click on the “Login with Google” button to sign in.
   - After successful authentication, you’ll be redirected to the main app view.

2. **Viewing Data**:
   - The data from your Google Sheet will load automatically.
   - Any changes made in the sheet will also appear here in real-time.

3. **Adding Data**:
   - Use the form to input new data and submit it.
   - This data will be added as a new row in the Google Sheet.

4. **Deleting Data**:
   - Click the delete icon next to any entry to remove it.
   - The row will be deleted in both the app and Google Sheets in real-time.

---

## Problem-Solving Approach and Solution Explanation

### 1. Approach to Solving the Problem

#### Step-by-Step Solution Breakdown

1. **OAuth2 Authentication**:
   - **Problem**: Securely connect users to their Google Sheets while maintaining a straightforward authentication process.
   - **Solution**: Implemented Google OAuth2 authentication via Laravel Socialite. This approach allows users to log in with their Google accounts, authorizing the app to access their Google Sheets without sharing their credentials directly with the application.
   - **Implementation Details**:
     - Google Client ID, Client Secret, and Redirect URI are configured in the Laravel `.env` file.
     - The login process redirects users to Google’s OAuth consent screen, after which Google sends back an access token for API requests.
     - The token is securely stored, allowing access to Google Sheets data for subsequent interactions.

2. **Google Sheets API Integration**:
   - **Problem**: The app needs to perform CRUD (Create, Read, Update, Delete) operations on Google Sheets with minimal latency and in a secure manner.
   - **Solution**: Using Google’s official API client library for PHP, set up endpoints in Laravel to manage data requests to and from Google Sheets.
   - **Implementation Details**:
     - Data from Google Sheets is fetched using the `spreadsheets.values.get` method, and new data is added via `spreadsheets.values.append`.
     - Updates and deletions are managed through the `spreadsheets.batchUpdate` API, which allows efficient handling of rows by specifying precise ranges.
     - These operations are secured by verifying user access tokens and handling Google API errors gracefully to provide feedback in the app.

3. **Real-Time Syncing**:
   - **Problem**: Ensuring that any changes made in the app or Google Sheets are reflected immediately to prevent data inconsistencies.
   - **Solution**: When I make any operation through the google api it's update realtime in google sheet.

4. **Frontend UI for CRUD Operations**:
   - **Problem**: The app must allow users to perform CRUD operations with a user-friendly, intuitive interface.
   - **Solution**: Vue.js was chosen for the frontend because of its reactive capabilities, and Vuex was used for state management to keep the app’s data synchronized.
   - **Implementation Details**:
     - A form component handles data entry, which posts data to the backend. A table component renders data from Google Sheets, allowing editing and deleting actions.
     - Vuex manages user authentication and token state, and Axios is used to handle API requests between Vue.js and Laravel.

---

### 2. Thought Process and Design Decisions

1. **Single Page Application (SPA) with Vue.js**:
   - **Reasoning**: SPAs provide a fast, seamless user experience, which is ideal for applications requiring real-time updates. Vue.js, with its powerful reactivity system and ease of integration with Laravel, was a natural choice to build the frontend.
   - **Benefits**: This architecture allows the app to load dynamically, responding quickly to user interactions without frequent page reloads, which is especially useful for managing data.

2. **Laravel Backend**:
   - **Reasoning**: Laravel’s robust MVC structure, easy handling of OAuth2, and integration with Socialite make it an excellent choice for securely managing user authentication and API endpoints.
   - **Benefits**: Laravel provides a strong foundation for security, data validation, and API development, streamlining communication between the app and Google Sheets.

3. **OAuth2 Authentication with Google Socialite**:
   - **Reasoning**: Directly implementing OAuth2 can be complex. Laravel Socialite simplifies OAuth integration with pre-configured methods, making Google authentication secure and straightforward.
   - **Benefits**: By using Socialite, ensure the security of user credentials and streamline the authentication process. It also enhances user trust, as they can sign in using their existing Google accounts.

4. **User Interface with Tailwind CSS**:
   - **Reasoning**: Tailwind CSS provides utility-first classes that make styling fast, consistent, and responsive. It helps us maintain a clean, minimalistic UI, which improves the overall user experience.
   - **Benefits**: Tailwind’s modular classes make it easy to apply styles and quickly adapt the layout for responsive design, ensuring the app is visually appealing and functional on any device.

5. **Error Handling and Security**:
   - **Reasoning**: Google Sheets API can experience issues (e.g., network errors, expired tokens). the solution ensures user-friendly error messages.
   - **Benefits**: This ensures users have a smooth experience even when errors occur, and their data remains secure.

---
