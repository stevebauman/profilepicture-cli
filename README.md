# ProfilePicture AI - CLI (Unofficial)

Download all of your 4k images from ProfilePicture AI automatically using a PHP CLI.

## Requirements

- PHP >= 8.0

## Usage

### Prepare Download Process:

1. Clone the repository
2. Open your web browser, open the dev console, and navigate to the "Network" tab
3. Navigate to https://ProfilePicture.AI
4. Capture the "token" response, and save it into a file named `credentials.json` inside the cloned repository root directory:

![Screenshot 2022-12-01 at 11 50 52 AM](https://user-images.githubusercontent.com/6421846/205112339-3d452858-dac1-425b-ba95-0cea67a8065f.png)

5. Navigate to the photos view of the person you've added, and capture the photos response and save it into a file named `images.json` inside the cloned repository root directory (the request will be named a numeric ID):

![Screenshot 2022-12-01 at 11 55 09 AM](https://user-images.githubusercontent.com/6421846/205112981-e72ea1f9-88ee-4806-80a3-31bd3e82b21e.png)

### Download Your 4k Images:

1. Open a terminal into the repository root
2. Run `composer install`
3. Run `php application download`
5. Press <kbd>Enter</kbd> to all the command line prompts
6. Progress will be initiated -- check the `downloads` folder for your 4k images

<img width="601" alt="Screenshot 2022-12-01 at 11 59 46 AM" src="https://user-images.githubusercontent.com/6421846/205113859-ac55d901-7d0d-4907-b866-d4b0e3e7aa23.png">
