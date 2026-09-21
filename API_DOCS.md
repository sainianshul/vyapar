# VyaparMitra - App Developer API Documentation

This document contains the complete and detailed flow of all APIs required for the mobile app development. It includes endpoints, methods, headers, parameters, and expected behaviors.

**Base URL:** `https://vyapar.rezel.online`

---

## 1. Authentication & Profile Flow

**Overview of the Flow:**

1. User enters mobile number -> App calls `send-otp`.
2. User enters OTP -> App calls `verify-otp`.
3. If `is_profile_complete` is `false` in the login response, App forces the user to the "Complete Profile" screen.
4. App calls `update-profile` to save the user's name, address, etc.
5. Once the profile is updated, the user is redirected to the Home Screen.

---

### 1.1 Send OTP

Used to send a 6-digit OTP to the user's mobile number.

- **Endpoint:** `/api/v1/auth/send-otp`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Content-Type: application/json`
- **Body Parameters (JSON):**
    - `phone` _(string, required)_: 10-digit mobile number.
- **Example Request:**
    ```json
    {
        "phone": "9876543210"
    }
    ```
- **Notes:** You can test with any random 10-digit number. In development mode, the OTP is returned in the success message.

---

### 1.2 Verify OTP (Login)

Used to verify the OTP and authenticate the user. It returns a Bearer Token which must be used for all subsequent API calls.

- **Endpoint:** `/api/v1/auth/verify-otp`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Content-Type: application/json`
- **Body Parameters (JSON):**
    - `phone` _(string, required)_: 10-digit mobile number.
    - `otp` _(string, required)_: 6-digit OTP received.
    - `device_id` _(string, required)_: Unique identifier for the user's device (e.g., UUID).
    - `device_name` _(string, optional)_: Name/Model of the device (e.g., "Samsung Galaxy S24").
    - `device_type` _(integer, optional)_: `1` for ANDROID, `2` for IOS.
    - `fcm_token` _(string, optional)_: Firebase Cloud Messaging token for push notifications.
    - `latitude` _(float, optional)_: Current location latitude.
    - `longitude` _(float, optional)_: Current location longitude.
- **Example Request:**
    ```json
    {
        "phone": "9876543210",
        "otp": "123456",
        "device_id": "abc-123-def",
        "device_name": "iPhone 15",
        "device_type": 2
    }
    ```
- **Important Response Fields:**
    - `token`: The Bearer token to save locally.
    - `is_profile_complete`: Boolean (`true` / `false`). If false, route the user to Profile Completion.

---

### 1.3 Update Profile

Used to update the user's basic details (Name, Address, Photo). This is a `multipart/form-data` request because it supports file uploads.

- **Endpoint:** `/api/v1/profile`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: multipart/form-data`
- **Form-Data Parameters:**
    - `name` _(string, required)_: Full Name of the user.
    - `address` _(string, optional)_: Detailed address.
    - `state` _(string, optional)_: State name.
    - `city` _(string, optional)_: City name.
    - `pincode` _(string, optional)_: Area pincode (digits).
    - `email` _(string, optional)_: Email address.
    - `latitude` _(float, optional)_: Location latitude.
    - `longitude` _(float, optional)_: Location longitude.
    - `profile_photo` _(file, optional)_: Image file (jpeg, png, jpg). Max 2MB.
- **Notes:** After calling this successfully the first time, the backend automatically marks `profile_completed_at`, so `is_profile_complete` becomes true for future logins.

---

### 1.4 Get My Profile (Me)

Fetch the latest profile data of the currently logged-in user.

- **Endpoint:** `/api/v1/auth/me`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`

---

### 1.5 Logout

Log out the user from the current device and invalidate the current token.

- **Endpoint:** `/api/v1/auth/logout`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`

---

### 1.6 Logout from All Devices

Log out the user from all devices (invalidates all tokens assigned to this user).

- **Endpoint:** `/api/v1/auth/logout-all`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`

---

## 2. Categories

**Overview of the Flow:**
Categories are used throughout the app to filter products and requirements. The category structure is a Tree (Parent -> Child -> Sub-child).

### 2.1 Get All Categories (Tree Structure)

Used to fetch all active categories. It returns root categories along with their children nested up to 2 levels deep.

- **Endpoint:** `/api/v1/categories`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Query Parameters:**
    - `parent_id` _(integer, optional)_: If passed, returns only the subcategories of this specific parent ID. If omitted, returns all root categories.
- **Example Request:**
  `GET /api/v1/categories?parent_id=5`
- **Important Response Fields:**
    - `categories`: Array of category objects. Each object contains a `children` array with its subcategories.

---

### 2.2 Get Featured Categories

Used to fetch only the "Featured" categories. This is typically used on the Home Screen of the app to show top categories.

- **Endpoint:** `/api/v1/categories/featured`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`

---

### 2.3 Get Single Category Details

Used to fetch the details of a single category by its ID, along with its active subcategories nested inside.

- **Endpoint:** `/api/v1/categories/{id}`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: The ID of the category.
- **Example Request:**
  `GET /api/v1/categories/12`

---

## 3. Products (Search, Browse & Seller Management)

**Overview of the Flow:**

- **Buyers** can search and browse products (`/api/v1/products`), see featured products, and view single product details. The search API supports location-based filtering (radius).
- **Sellers** can add their own products (`POST /api/v1/products`), view their own products (`/api/v1/my/products`), edit them, update their statuses, and delete them.

### 3.1 Browse & Search Products (For Buyers)

Used to fetch a paginated list of products with extensive filtering and sorting capabilities.

- **Endpoint:** `/api/v1/products`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>` _(optional for public search, but good practice)_
- **Query Parameters (All Optional):**
    - `category_id` _(integer)_: Filter by a specific category.
    - `search` _(string)_: Search text (matches product title/tags/other category name).
    - `condition` _(integer)_: `1` for New, `2` for Used.
    - `price_min` _(float)_: Minimum price filter.
    - `price_max` _(float)_: Maximum price filter.
    - `city` _(string)_: Exact match for the city name.
    - `latitude` _(float)_, `longitude` _(float)_, `radius` _(integer)_: Must pass all three together to find products within a specific radius (in KM) from the given coordinates.
    - `sort` _(string)_: Sorting order. Allowed values: `latest` (default), `price_low`, `price_high`, `nearest` (requires lat/lng).
    - `page` _(integer)_: Pagination page number (default 1).
    - `per_page` _(integer)_: Items per page (default 20, max 50).
- **Example Request:**
  `GET /api/v1/products?search=iphone&price_max=50000&sort=price_low&page=1`

---

### 3.2 Get Featured Products

Used to show highlighted products on the Home Screen.

- **Endpoint:** `/api/v1/products/featured`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`

---

### 3.3 Get Single Product Details (For Buyers)

Used when a buyer taps on a product. Automatically increments the product's `views_count`.

- **Endpoint:** `/api/v1/products/{id}`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: Product ID.

---

### 3.4 Add New Product (For Sellers)

Used by sellers to add a new product. This is a `multipart/form-data` request.

- **Endpoint:** `/api/v1/products`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: multipart/form-data`
- **Form-Data Parameters:**
    - `category_id` _(integer, required)_: Selected Category ID.
   - `other_category_name` _(string, optional)_: Name of the category if "Other" is selected.
    - `title` _(string, required)_: Name/Title of the product.
    - `price` _(float, required)_: Price of the product.
    - `condition` _(integer, required)_: `1` (New) or `2` (Used).
    - `description` _(string, optional)_: Detailed description.
    - `price_unit` _(string, optional)_: e.g., 'per piece', 'per kg'.
    - `is_negotiable` _(boolean, optional)_: `1` (true) or `0` (false).
    - `minimum_quantity` _(integer, optional)_: Minimum order quantity.
    - `location` _(string, optional)_: Address/Location line.
    - `city` _(string, optional)_: City name.
    - `pincode` _(string, optional)_: Area pincode.
    - `latitude` _(float, optional)_: GPS Latitude.
    - `longitude` _(float, optional)_: GPS Longitude.
    - `primary_image` _(file, optional)_: Main image file (max 2MB).
    - `additional_images[]` _(file array, optional)_: Up to 5 extra images.
- **Example (Sending Array via Postman/Dio):**
  Use key `additional_images[]` multiple times for multiple files.

---

### 3.5 Get My Products (For Sellers)

Used by sellers to manage their own products in their "My Listings" section.

- **Endpoint:** `/api/v1/my/products`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Query Parameters (All Optional):**
    - `status` _(integer)_: Filter by status (1: Active, 2: Inactive, 3: Sold, 4: Draft).
    - `search` _(string)_: Search text.
    - `sort` _(string)_: `latest` (default), `oldest`, `most_viewed`, `most_leads`.
    - `page` _(integer)_: Pagination page number.

---

### 3.6 Get My Single Product Details (For Sellers)

Used by sellers to fetch details of a specific product they own (usually before editing it).

- **Endpoint:** `/api/v1/my/products/{id}`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: Product ID.

---

### 3.7 Update Product (For Sellers)

Used by sellers to edit a product. Also a `multipart/form-data` request.

- **Endpoint:** `/api/v1/products/{id}`
- **Method:** `POST` _(Note: Laravel handles file uploads better with POST. Do NOT use PUT/PATCH for multipart)_
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: multipart/form-data`
- **Path Parameters:**
    - `id` _(integer, required)_: Product ID.
- **Form-Data Parameters (All Optional for Update):**
    - `category_id`, `other_category_name`, `title`, `price`, `condition`, `description`, etc. (Same as Add Product).
    - `primary_image` _(file)_: New main image.
    - `additional_images[]` _(file array)_: New additional images to append.
    - `deleted_images[]` _(integer array)_: Pass IDs of existing additional images you want to delete.

---

### 3.8 Update Product Status (For Sellers)

Used by sellers to quickly mark a product as Sold, Inactive, or Active.

- **Endpoint:** `/api/v1/my/products/{id}/status`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: application/json`
- **Path Parameters:**
    - `id` _(integer, required)_: Product ID.
- **Body Parameters (JSON):**
    - `status` _(integer, required)_: `1` (Active), `2` (Inactive), `3` (Sold), `4` (Draft).

---

### 3.9 Delete Product (For Sellers)

Used to permanently delete a product and all its associated images.

- **Endpoint:** `/api/v1/my/products/{id}`
- **Method:** `DELETE`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: Product ID.

---

## 4. Requirements (Post Buying Needs & Seller Browsing)

**Overview of the Flow:**

- **Buyers** can post their buying needs (`POST /api/v1/requirements`), manage their own requirements (`/api/v1/my/requirements`), edit them, and delete them.
- **Sellers** can browse these requirements (`/api/v1/requirements`) to find buyers looking for products they sell, using filters like location and category.

### 4.1 Browse & Search Requirements (For Sellers)

Used by sellers to find buyers' requirements. Similar to product search but for requirements.

- **Endpoint:** `/api/v1/requirements`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Query Parameters (All Optional):**
    - `category_id` _(integer)_: Filter by a specific category.
    - `search` _(string)_: Search text (matches title/tags/other category name).
    - `city` _(string)_: Exact match for the city name.
    - `latitude` _(float)_, `longitude` _(float)_, `radius` _(integer)_: Must pass all three together to find requirements within a specific radius (in KM).
    - `sort` _(string)_: Sorting order. Allowed values: `latest` (default), `nearest` (requires lat/lng).
    - `page` _(integer)_: Pagination page number (default 1).
    - `per_page` _(integer)_: Items per page (default 20, max 50).
- **Example Request:**
  `GET /api/v1/requirements?search=tshirts&radius=50&latitude=28.6&longitude=77.2`

---

### 4.2 Get Single Requirement Details

Used to view the full details of a specific requirement.

- **Endpoint:** `/api/v1/requirements/{id}`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: Requirement ID.

---

### 4.3 Add New Requirement (For Buyers)

Used by buyers to post a new buying need. This is a `multipart/form-data` request.

- **Endpoint:** `/api/v1/requirements`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: multipart/form-data`
- **Form-Data Parameters:**
    - `title` _(string, required)_: Brief title (e.g., "Need 100 T-shirts").
    - `category_id` _(integer, required)_: Related Category ID.
   - `other_category_name` _(string, optional)_: Name of the category if "Other" is selected.
    - `description` _(string, required)_: Detailed description of what is needed.
    - `quantity` _(integer, optional)_: Approximate quantity needed (e.g., 100).
    - `target_budget` _(float, required)_: Total budget or price expectation.
    - `delivery_location` _(string, optional)_: Full address for delivery.
    - `delivery_pincode` _(string, optional)_: Area pincode for delivery.
    - `city` _(string, optional)_: Delivery City.
    - `latitude` _(float, optional)_: GPS Latitude for delivery.
    - `longitude` _(float, optional)_: GPS Longitude for delivery.
    - `images[]` _(file array, optional)_: Up to 5 images for reference. (Use key `images[]` multiple times for multiple files).

---

### 4.4 Get My Requirements (For Buyers)

Used by buyers to manage their own posted requirements.

- **Endpoint:** `/api/v1/my/requirements`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`

---

### 4.5 Get My Single Requirement Details (For Buyers)

Used to fetch details of a specific requirement before editing it.

- **Endpoint:** `/api/v1/my/requirements/{id}`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: Requirement ID.

---

### 4.6 Update Requirement (For Buyers)

Used to edit an existing requirement. Also a `multipart/form-data` request.

- **Endpoint:** `/api/v1/my/requirements/{id}`
- **Method:** `POST` _(Note: Use POST for multipart form data, Laravel handles it automatically)_
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: multipart/form-data`
- **Path Parameters:**
    - `id` _(integer, required)_: Requirement ID.
- **Form-Data Parameters (All Optional for Update):**
    - `title`, `category_id`, `other_category_name`, `description`, `quantity`, `target_budget`, etc. (Same as Add Requirement).
    - `new_images[]` _(file array)_: New images to append.
    - `deleted_images[]` _(integer array)_: IDs of existing images you want to delete.

---

### 4.7 Delete Requirement (For Buyers)

Used to permanently delete a requirement and its images.

- **Endpoint:** `/api/v1/my/requirements/{id}`
- **Method:** `DELETE`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: Requirement ID.

---

## 5. Leads & Enquiries

**Overview of the Flow:**

- Whenever a buyer interacts with a seller's product (e.g., sends a Chat, makes a Call, clicks "View Number", or submits an Enquiry Form), a **Lead** is automatically captured.
- **Sellers** can view all leads they received, filter them by source or status, and update their statuses (New, Contacted, Converted, Rejected) to manage their sales pipeline.
- **Buyers** can view a history of all leads/inquiries they have sent.

### 5.1 Capture a Lead / Send Enquiry (For Buyers)

Used to manually submit an enquiry form or trigger a lead when a user taps "View Number". (Note: Calling and Chatting automatically capture leads via their respective APIs, so you don't need to call this manually for those).

- **Endpoint:** `/api/v1/leads`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: application/json`
- **Body Parameters (JSON):**
    - `seller_id` _(integer, required)_: ID of the user selling the product.
    - `source` _(integer, required)_: `1` (Inquiry Form), `2` (Call), `3` (Chat), `4` (View Number).
    - `product_id` _(integer, optional)_: Product ID (if inquiring about a product).
    - `requirement_id` _(integer, optional)_: Requirement ID (if inquiring about a requirement).
    - `quantity` _(integer, optional)_: Quantity requested.
    - `message` _(string, optional)_: Custom message from the buyer.
- **Example Request:**
    ```json
    {
        "seller_id": 2,
        "source": 1,
        "product_id": 15,
        "message": "What is your best price for bulk?"
    }
    ```

---

### 5.2 Get Received Leads (For Sellers)

Used by sellers to see everyone who showed interest in their products.

- **Endpoint:** `/api/v1/my/leads/received`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Query Parameters (All Optional):**
    - `status` _(integer)_: Filter by status (1: New, 2: Contacted, 3: Converted, 4: Rejected).
    - `source` _(integer)_: Filter by source (1: Inquiry Form, 2: Call, 3: Chat, 4: View Number).
    - `temperature` _(integer)_: 1: Cold, 2: Warm, 3: Hot.
    - `page` _(integer)_: Pagination page number.

---

### 5.3 Get Only Enquiries (For Sellers)

Shortcut endpoint to get only the leads that came specifically from the "Enquiry Form" (`source = 1`). Used to populate the "Enquiries" list.

- **Endpoint:** `/api/v1/my/enquiries`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Query Parameters (All Optional):**
    - `page` _(integer)_: Pagination page number.

---

### 5.4 Get Leads for a Specific Product (For Sellers)

Used when a seller views their own product details and wants to see all leads specifically generated for that one product.

- **Endpoint:** `/api/v1/my/products/{id}/leads`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: Product ID.

---

### 5.5 Update Lead Status (For Sellers)

Used by sellers to organize their sales funnel (e.g., marking a lead as "Contacted" or "Converted").

- **Endpoint:** `/api/v1/my/leads/{id}/status`
- **Method:** `PUT`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: application/json`
- **Path Parameters:**
    - `id` _(integer, required)_: Lead ID.
- **Body Parameters (JSON):**
    - `status` _(integer, required)_: `1` (New), `2` (Contacted), `3` (Converted), `4` (Rejected).

---

### 5.6 Get Sent Leads / My Inquiries (For Buyers)

Used by buyers to see a history of all products they have inquired about, called, or chatted regarding.

- **Endpoint:** `/api/v1/my/leads/sent`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Query Parameters (All Optional):**
    - `status`, `source`, `temperature`, `page` (Same as Received Leads).

---

## 6. Chat (Conversations & Realtime Messaging)

**Overview of the Flow:**

1. A buyer taps "Chat" on a product -> App calls `startConversation` API.
2. API returns the `conversation_id`. App opens the chat screen and fetches old messages via `getMessages` API.
3. To send a message, App calls `sendMessage` API.
4. **WebSockets (Pusher):** App must connect to Laravel Echo / Pusher to receive realtime messages.
5. When the user opens the chat or receives a message while inside the chat, App calls `markAsRead` API to clear unread counts.

### 6.1 Start a Conversation

Used to initiate a chat. If a chat already exists between the two users for the given product/requirement, it returns the existing one instead of creating a duplicate. _(Note: Calling this automatically captures a Lead on the backend!)_

- **Endpoint:** `/api/v1/chat/conversations`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: application/json`
- **Body Parameters (JSON):**
    - `seller_id` _(integer, required)_: The user you want to chat with.
    - `product_id` _(integer, optional)_: Product ID context.
    - `requirement_id` _(integer, optional)_: Requirement ID context.
- **Example Request:**
    ```json
    {
        "seller_id": 2,
        "product_id": 15
    }
    ```

---

### 6.2 Get List of Conversations (Inbox)

Used to populate the "Chats / Inbox" screen. Returns a list of conversations sorted by latest message, along with unread counts.

- **Endpoint:** `/api/v1/chat/conversations`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Query Parameters:**
    - `page` _(integer, optional)_: Pagination page number.

---

### 6.3 Get Messages of a Conversation

Used to fetch the chat history inside a specific chat screen. _(Note: Calling this automatically marks the conversation as read for the logged-in user)._

- **Endpoint:** `/api/v1/chat/conversations/{id}/messages`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: Conversation ID.
- **Query Parameters:**
    - `page` _(integer, optional)_: Pagination page number. (Messages are returned latest first).

---

### 6.4 Send a Message

Used to send a text or image message. This is a `multipart/form-data` request.

- **Endpoint:** `/api/v1/chat/conversations/{id}/messages`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: multipart/form-data`
- **Path Parameters:**
    - `id` _(integer, required)_: Conversation ID.
- **Form-Data Parameters:**
    - `type` _(integer, required)_: `1` (Text), `2` (Image), `3` (Call Log - System generated usually).
    - `body` _(string, required if type=1)_: Text message content.
    - `media` _(file, required if type=2)_: Image file (max 5MB).

---

### 6.5 Mark Conversation as Read

Used to manually mark a conversation as read. (e.g., if a new message arrives via WebSocket while the user is already looking at the chat screen, you should hit this silently).

- **Endpoint:** `/api/v1/chat/conversations/{id}/read`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Path Parameters:**
    - `id` _(integer, required)_: Conversation ID.

---

### 6.6 Realtime WebSocket Details (Pusher)

To receive messages in real-time without refreshing, the app must connect to Pusher WebSocket.

**Connection Credentials:**

- `PUSHER_APP_KEY`: `local`
- `PUSHER_HOST`: `vyapar.rezel.online`
- `PUSHER_PORT`: `443` (WSS)
- `CLUSTER`: `mt1`
- `ForceTLS`: `true`

**Authentication (For Private Channels):**
Since it's a private chat, Pusher needs authentication.

- **Auth Endpoint:** `POST https://vyapar.rezel.online/broadcasting/auth`
- **Auth Header:** Pass the `Authorization: Bearer <token>` in the Pusher auth config.

**Channel & Event Binding:**

- The logged-in user should subscribe to their own private channel.
- **Channel Name:** `private-vyaparmitra_user.{your_user_id}` (e.g., `private-vyaparmitra_user.34`)
- **Event Name:** `message.sent`

**WebSocket Expected Payload:**
When someone sends a message to you, you will receive this payload on the socket:

```json
{
    "message": {
        "id": 12,
        "conversation_id": 5,
        "sender_id": 2,
        "type": 1,
        "body": "Hello there!",
        "media_url": null,
        "created_at": "2026-09-18T10:15:30Z"
    },
    "conversation_id": 5
}
```

**Handling Logic in App:**

1. If the user is currently on the Chat Screen for `conversation_id = 5`, append the message to the UI instantly and call the `markAsRead` API in the background.
2. If the user is on the Inbox Screen or anywhere else, update the unread badge counter.

---

## 7. WebRTC Audio / Video Calling

**Overview of the Flow:**
The app uses WebRTC for peer-to-peer calling, and Laravel API + Pusher WebSocket to exchange signaling data (SDP offers/answers and ICE candidates).

1. **Caller** hits `POST /api/v1/call/initiate`. Backend creates a call log and fires `incoming-call` WebSocket event to the receiver.
2. **Receiver** gets the `incoming-call` event. Their phone rings.
3. **Receiver** can hit `POST /api/v1/call/answer` with `action="accept"` or `action="reject"`. This fires `call-answered` WebSocket event back to the caller.
4. If accepted, both peers start generating WebRTC signals. They send these signals to `POST /api/v1/call/signal`, which relays them to the other person via the `webrtc-signal` WebSocket event.
5. When anyone hangs up, they hit `POST /api/v1/call/end`. Backend fires `call-ended` WebSocket event.

### 7.1 Initiate a Call

Used by the Caller to start ringing the Receiver. (Automatically captures a Lead).

- **Endpoint:** `/api/v1/call/initiate`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: application/json`
- **Body Parameters (JSON):**
    - `receiver_id` _(integer, required)_: User ID of the person to call.
    - `call_type` _(string, required)_: `audio` or `video`.
    - `product_id` _(integer, optional)_: Product ID context.
    - `requirement_id` _(integer, optional)_: Requirement ID context.

---

### 7.2 Answer or Reject a Call

Used by the Receiver when they tap "Accept" or "Decline" on the incoming call screen.

- **Endpoint:** `/api/v1/call/answer`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: application/json`
- **Body Parameters (JSON):**
    - `call_id` _(integer, required)_: ID of the call from the incoming socket event.
    - `action` _(string, required)_: `accept` or `reject`.

---

### 7.3 Relay WebRTC Signal

Used by both peers to exchange SDP Offer, SDP Answer, and ICE Candidates.

- **Endpoint:** `/api/v1/call/signal`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: application/json`
- **Body Parameters (JSON):**
    - `call_id` _(integer, required)_: Call ID.
    - `signal_type` _(string, required)_: `offer`, `answer`, or `ice-candidate`.
    - `signal_data` _(string, required)_: The JSON stringified WebRTC payload (SDP or ICE dict).

---

### 7.4 End an Active Call

Used when someone taps the Red hangup button.

- **Endpoint:** `/api/v1/call/end`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: application/json`
- **Body Parameters (JSON):**
    - `call_id` _(integer, required)_: Call ID.
    - `reason` _(string, optional)_: `caller_ended`, `receiver_ended`, or `error`.

---

### 7.5 Mark Ringing Call as Missed (Timeout)

Used by the Caller if the Receiver does not answer after 30-60 seconds.

- **Endpoint:** `/api/v1/call/missed`
- **Method:** `POST`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
    - `Content-Type: application/json`
- **Body Parameters (JSON):**
    - `call_id` _(integer, required)_: Call ID.

---

### 7.6 Calling History & Active Call Check

- **Get History:** `GET /api/v1/call/history` (Takes `page` parameter).
- **Check Active Call:** `GET /api/v1/call/active` (Useful when app resumes from background to check if they are still in a call).

---

### 7.7 Calling WebSocket Events (Pusher)

App must be connected to the exact same Pusher config as Chat, listening on `private-vyaparmitra_user.{user_id}`.

You will receive these 4 events:

**1. `incoming-call`**
Fired when someone calls you. Payload contains `callId`, `callerId`, `callerName`, `callerAvatar`, `callType`.

**2. `call-answered`**
Fired to the caller when the receiver accepts or rejects. Payload contains `action` ("accept" or "reject").

**3. `webrtc-signal`**
Fired during the active call setup to exchange SDP and ICE. Payload contains `signalType` and `signalData`. App must parse `signalData` and pass it to the WebRTC PeerConnection.

**4. `call-ended`**
Fired when the other person hangs up. Payload contains `reason`. App should close WebRTC connection and show Call Ended screen.

---

## 8. Buyer Home (Single Fast Endpoint)

**Overview:**
To provide a fast loading experience on the buyer's home screen, a single API is used to fetch banners, featured categories, nearby products, and feedbacks all at once. The result is heavily cached (for 5 minutes) based on a rounded ~1km grid of the user's location.

### 8.1 Buyer Home API

- **Endpoint:** `/api/v1/home`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Query Parameters (Optional):**
    - `latitude` _(float)_: GPS Latitude.
    - `longitude` _(float)_: GPS Longitude.
- **Behavior:**
    - Attempts to find 10 products within expanding radiuses (10km, 50km, 100km).
    - If it finds fewer than 10 nearby, it pulls fallback products (featured/latest) to always return exactly 10 products, sorted strictly by distance (closest first).
- **Response Structure (`data` object):**
    - `banners`: Array of active banners.
    - `categories`: Array of 10 featured categories.
    - `products`: Array of 10 products sorted by distance.
    - `products_source`: String indicating how products were fetched (e.g. `nearby_10km`, `fallback`, `nearby_and_fallback`).
    - `feedbacks`: Array of 5 active user feedbacks.

---

## 9. Banners & Feedbacks (Standalone APIs)

### 9.1 Get Active Banners

Used if banners need to be fetched separately (outside of the Home API).

- **Endpoint:** `/api/v1/banners`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`

### 9.2 Get Active Feedbacks

Used if feedbacks need to be fetched separately (outside of the Home API).

- **Endpoint:** `/api/v1/feedbacks`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`

---

## 10. Dashboard (For Sellers)

**Overview:**
Provides aggregate statistics for the seller to show on their Home Screen. The data is cached for 5 minutes on the backend to improve performance.

### 10.1 Get Seller Dashboard Stats

- **Endpoint:** `/api/v1/dashboard/seller`
- **Method:** `GET`
- **Headers:**
    - `Accept: application/json`
    - `Authorization: Bearer <your_token>`
- **Response Structure (`dashboard` object):**
    - `total_products`: Total active/inactive products listed by the seller.
    - `total_leads_received`: Total leads from all sources (calls, chats, inquiries).
    - `total_enquiries_received`: Total leads specifically from the Inquiry Form (`source = 1`).
    - `total_product_views`: Sum of all views across all products.
    - `total_unread_chats`: Number of conversations with unread messages.
    - `most_viewed_product`: Details of their most popular product (Product Object).
    - `recent_leads`: Array of the 5 most recent leads.

---

## 11. System / Utility Endpoints

### 11.1 Server Health Check

Check if the API is up and running. No authentication required.

- **Endpoint:** `/health`
- **Method:** `GET`

---

_End of Documentation_
