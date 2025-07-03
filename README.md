# অন্তঃকণ্ঠ - Bengali Poetry Platform

![অন্তঃকণ্ঠ Logo](https://antokantho-poems.onderender.com/assets/images/logo.png)

অন্তঃকণ্ঠ is a Bengali poetry social platform where poets can share their work, connect with readers, and discover beautiful Bengali poetry. This platform features user profiles, poem publishing, likes/comments, and a robust API for developers.

**Live Website**: [antokantho-poems.onderender.com](https://antokantho-poems.onderender.com)  
**API Endpoint**: `https://antokantho-poems.onderender.com/api/api.php?`

## Table of Contents
1. [User Registration](#user-registration)
2. [Poem Upload](#poem-upload)
3. [API Usage](#api-usage)
   - [JavaScript](#javascript)
   - [PHP](#php)
   - [Python](#python)
   - [Bash](#bash)
   - [C++](#c)
   - [HTML](#html)
   - [GoatBot](#goatbot)
   - [Mirai Bot](#mirai-bot)
4. [Examples](#examples)

## User Registration
1. Visit [antokantho-poems.onderender.com/login.php?register](https://antokantho-poems.onderender.com/login.php?register)
2. Fill in your details:
   - Username (must be unique)
   - Password
   - Email address
   - Profile information
3. Click "রেজিস্টার করুন" (Register)
4. Verify your email (if enabled)
5. Log in with your credentials

## Poem Upload
1. Log in to your account
2. Click "নতুন কবিতা" (New Poem) in the navigation menu
3. Fill in poem details:
   - শিরোনাম (Title)
   - ধরণ (Category - প্রেম/প্রকৃতি/জীবন/etc.)
   - কবিতার বিষয়বস্তু (Poem Content)
4. Click "প্রকাশ করুন" (Publish)
5. Your poem will appear on the homepage and your profile

## API Usage
Access Bengali poems through our RESTful API. The base URL is:

```
https://antokantho-poems.onderender.com/api/api.php?
```

### Parameters
| Parameter | Required | Values | Description |
|-----------|----------|--------|-------------|
| `content` | Optional | `random` (default), `time`, `name`, `length` | Sorting method |
| `total`   | Optional | 1-10 (default=1) | Number of poems to return |
| `user`    | Optional | Valid username | Filter by specific user |

### Response Format
Successful response:
```json
{
  "status": "success",
  "title": "কবিতার শিরোনাম",
  "poem": "কবিতার সম্পূর্ণ বিষয়বস্তু...",
  "writter": "কবির_নাম",
  "api": "Api owned by Chitron Bhattacharjee"
}
```

Multiple poems response:
```json
{
  "status": "success",
  "poems": [
    {
      "title": "প্রথম কবিতা",
      "poem": "কবিতার বিষয়বস্তু...",
      "writter": "কবি_১"
    },
    {
      "title": "দ্বিতীয় কবিতা",
      "poem": "কবিতার বিষয়বস্তু...",
      "writter": "কবি_২"
    }
  ],
  "api": "Api owned by Chitron Bhattacharjee"
}
```

Error response:
```json
{
  "status": "error",
  "message": "Error description",
  "debug": {
    "error_type": "Exception",
    "file": "api.php",
    "line": 42,
    "trace": [...]
  },
  "api": "Api owned by Chitron Bhattacharjee"
}
```

### Examples

#### JavaScript
```javascript
// Using Fetch API
async function getPoem() {
  const response = await fetch(
    'https://antokantho-poems.onderender.com/api/api.php?content=random&total=1'
  );
  const data = await response.json();
  
  if(data.status === 'success') {
    console.log(`Title: ${data.title}`);
    console.log(`Poet: ${data.writter}`);
    console.log(`Poem: ${data.poem}`);
  } else {
    console.error(`Error: ${data.message}`);
  }
}

getPoem();
```

#### PHP
```php
<?php
$url = 'https://antokantho-poems.onderender.com/api/api.php?content=time&total=2';
$response = file_get_contents($url);
$data = json_decode($response, true);

if($data['status'] === 'success') {
    if(isset($data['poems'])) {
        foreach($data['poems'] as $poem) {
            echo "Title: " . $poem['title'] . "\n";
            echo "Poet: " . $poem['writter'] . "\n";
            echo "Poem: " . $poem['poem'] . "\n\n";
        }
    } else {
        echo "Title: " . $data['title'] . "\n";
        echo "Poet: " . $data['writter'] . "\n";
        echo "Poem: " . $data['poem'] . "\n";
    }
} else {
    echo "Error: " . $data['message'];
}
?>
```

#### Python
```python
import requests

url = "https://antokantho-poems.onderender.com/api/api.php"
params = {
    "content": "random",
    "total": 3,
    "user": "shovon_bhattacharjee"
}

response = requests.get(url, params=params)
data = response.json()

if data['status'] == 'success':
    if 'poems' in data:
        for poem in data['poems']:
            print(f"Title: {poem['title']}")
            print(f"Poet: {poem['writter']}")
            print(f"Poem: {poem['poem']}\n")
    else:
        print(f"Title: {data['title']}")
        print(f"Poet: {data['writter']}")
        print(f"Poem: {data['poem']}")
else:
    print(f"Error: {data['message']}")
```

#### Bash
```bash
# Get 2 random poems
curl "https://antokantho-poems.onderender.com/api/api.php?content=random&total=2"

# Get latest poem by a specific user
curl "https://antokantho-poems.onderender.com/api/api.php?content=time&total=1&user=shovon_bhattacharjee"
```

#### C++
```cpp
#include <iostream>
#include <curl/curl.h>
#include <json/json.h>

size_t WriteCallback(void* contents, size_t size, size_t nmemb, std::string* output) {
    size_t total_size = size * nmemb;
    output->append((char*)contents, total_size);
    return total_size;
}

int main() {
    CURL* curl;
    CURLcode res;
    std::string api_response;
    
    curl = curl_easy_init();
    if(curl) {
        std::string url = "https://antokantho-poems.onderender.com/api/api.php?content=random&total=1";
        curl_easy_setopt(curl, CURLOPT_URL, url.c_str());
        curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, WriteCallback);
        curl_easy_setopt(curl, CURLOPT_WRITEDATA, &api_response);
        
        res = curl_easy_perform(curl);
        if(res != CURLE_OK) {
            std::cerr << "cURL error: " << curl_easy_strerror(res) << std::endl;
        } else {
            Json::Value root;
            Json::CharReaderBuilder builder;
            std::istringstream json_stream(api_response);
            std::string errors;
            
            if(Json::parseFromStream(builder, json_stream, &root, &errors)) {
                if(root["status"].asString() == "success") {
                    std::cout << "Title: " << root["title"].asString() << std::endl;
                    std::cout << "Poet: " << root["writter"].asString() << std::endl;
                    std::cout << "Poem: " << root["poem"].asString() << std::endl;
                } else {
                    std::cerr << "Error: " << root["message"].asString() << std::endl;
                }
            } else {
                std::cerr << "JSON parse error: " << errors << std::endl;
            }
        }
        curl_easy_cleanup(curl);
    }
    return 0;
}
```

#### HTML
```html
<!DOCTYPE html>
<html>
<head>
    <title>Bengali Poetry API Example</title>
</head>
<body>
    <h1>Random Bengali Poem</h1>
    <div id="poem-container">Loading...</div>

    <script>
        async function loadPoem() {
            try {
                const response = await fetch(
                    'https://antokantho-poems.onderender.com/api/api.php?content=random&total=1'
                );
                const data = await response.json();
                
                if(data.status === 'success') {
                    document.getElementById('poem-container').innerHTML = `
                        <h2>${data.title}</h2>
                        <h3>By: ${data.writter}</h3>
                        <pre>${data.poem}</pre>
                    `;
                } else {
                    document.getElementById('poem-container').innerHTML = `
                        <p class="error">Error: ${data.message}</p>
                    `;
                }
            } catch (error) {
                document.getElementById('poem-container').innerHTML = `
                    <p class="error">Network error: ${error.message}</p>
                `;
            }
        }
        
        loadPoem();
    </script>
</body>
</html>
```

#### GoatBot
```python
# GoatBot plugin for Bengali poetry
import requests

def poetry_command(bot, command, args):
    if command == "!poem":
        try:
            # Get random poem
            response = requests.get(
                "https://antokantho-poems.onderender.com/api/api.php?content=random&total=1"
            )
            data = response.json()
            
            if data['status'] == 'success':
                return f"📜 {data['title']} - {data['writter']}\n\n{data['poem']}"
            else:
                return f"❌ Error: {data['message']}"
                
        except Exception as e:
            return f"⚠️ Network error: {str(e)}"
    
    elif command == "!poet":
        if not args:
            return "Please specify a poet: !poet [username]"
        
        try:
            # Get poem by specific poet
            response = requests.get(
                f"https://antokantho-poems.onderender.com/api/api.php?user={args[0]}&total=1"
            )
            data = response.json()
            
            if data['status'] == 'success':
                return f"📜 {data['title']} - {data['writter']}\n\n{data['poem']}"
            else:
                return f"❌ Error: {data['message']}"
                
        except Exception as e:
            return f"⚠️ Network error: {str(e)}"
```

#### Mirai Bot
```java
// Mirai Bot plugin for Bengali poetry
import net.mamoe.mirai.event.events.GroupMessageEvent;
import net.mamoe.mirai.message.data.PlainText;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.Response;
import org.json.JSONObject;

public class BengaliPoetryPlugin extends JavaPlugin {
    
    private final OkHttpClient httpClient = new OkHttpClient();
    
    @Override
    public void onEnable() {
        getLogger().info("Bengali Poetry Plugin loaded!");
    }
    
    @EventHandler
    public void onGroupMessage(GroupMessageEvent event) {
        String message = event.getMessage().contentToString();
        
        if (message.startsWith("!কবিতা")) {
            try {
                // Get random poem
                Request request = new Request.Builder()
                    .url("https://antokantho-poems.onderender.com/api/api.php?content=random&total=1")
                    .build();
                
                try (Response response = httpClient.newCall(request).execute()) {
                    if (response.body() != null) {
                        JSONObject data = new JSONObject(response.body().string());
                        
                        if (data.getString("status").equals("success")) {
                            String reply = "📜 " + data.getString("title") + "\n" +
                                          "✍️ কবি: " + data.getString("writter") + "\n\n" +
                                          data.getString("poem");
                            
                            event.getGroup().sendMessage(new PlainText(reply));
                        } else {
                            event.getGroup().sendMessage(new PlainText(
                                "❌ ত্রুটি: " + data.getString("message")
                            ));
                        }
                    }
                }
            } catch (Exception e) {
                event.getGroup().sendMessage(new PlainText(
                    "⚠️ নেটওয়ার্ক ত্রুটি: " + e.getMessage()
                ));
            }
        }
    }
}
```

## Examples
### Get a random poem
```
https://antokantho-poems.onderender.com/api/api.php?content=random&total=1
```

### Get 3 newest poems
```
https://antokantho-poems.onderender.com/api/api.php?content=time&total=3
```

### Get longest poem by a specific user
```
https://antokantho-poems.onderender.com/api/api.php?content=length&user=shovon_bhattacharjee&total=1
```

### Get poems alphabetically sorted
```
https://antokantho-poems.onderender.com/api/api.php?content=name&total=5
```

## Contact
For API issues or website support, contact:  
**Chitron Bhattacharjee**  
[chitronbhattacharjee@gmail.com](mailto:chitronbhattacharjee@gmail.com)

**API Ownership**: Api owned by Chitron Bhattacharjee

```

This README provides comprehensive documentation for using the অন্তঃকণ্ঠ website and API, including:
1. User registration and poem upload instructions
2. API endpoint and parameters
3. Response formats for both success and error cases
4. Code examples in 8 different programming languages/environments
5. Practical usage examples

The documentation is structured to be easy to follow for both end users and developers, with Bengali terms maintained where appropriate for authenticity. The API examples cover all requested languages and bots, with special attention to the Bengali poetry context.
