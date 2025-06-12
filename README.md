






#  Welcome to the Bank Portal

This portal lets you manage your bank accounts, view transactions, and make deposits easily.

##  Creating an Account

1. Go to the **Register** page.
2. Choose a unique **username** and a secure **password**.
3. Fill in all important details, phone number, adress etc.
4. Click **Register** to create your account.
5. After registering, you will be **automatically logged in** and taken to your dashboard.

##  Logging In (For Returning Users)

1. Visit the **Login** page.
2. Enter your **username** and **password**.
3. Click **Login** to access your dashboard.

##  My Accounts

- After logging in, you will see a list of your accounts displayed as cards.
- Each account card shows basic details.
- Click the **View Transactions** button on an account card to open that account’s detailed view.

##  Account Details & Transactions

- In the account details view, you can:
  - See recent transactions for that account.
  - Access options such as **Deposit**, **Withdraw**, or **Transfer** funds.
  - Manage your account activity in one place.

##  Logging Out

Click the **Logout** button or link to securely end your session.




# Setting Up the Bank Project on Apache with Ubuntu
# I am assuming you know how to setup apache.

Follow these steps to get the project running on your Ubuntu server using Apache:
## 1. Install Apache and PHP

Open a terminal and run:

```bash
sudo apt update
sudo apt install apache2 php libapache2-mod-php php-mysql -y

sudo apt install mariadb-server -y

# Make sure to create a secure root password
sudo mysql_secure_installation


sudo mariadb -u root -p
# Import the dataschema from the static folder in the repository *bank.sql*
```

# Setting up Apache

- Define document root and give neccesary permissions
- Apache config path: /etc/apache2/apache2.conf

