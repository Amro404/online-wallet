#  🏦 Online Wallet Application

An online wallet application that handles incoming bank webhooks, processes transactions, and generates payment request XMLs for outgoing transfers.

## Overview

This project implements an online wallet application with core functionality to:
- Receive money through bank webhooks
- Process transactions in bulk from multiple banking partners
- Maintain accurate client balances while handling duplicate transactions
- Ensure no webhook data is lost during processing pauses

## 🚀 Features

### Webhook Processing
- Accepts webhook requests from multiple bank formats
- Parses transactions with different data formats
- Handles multiple transactions in a single webhook call
- Implements idempotency to prevent duplicate processing

### Wallet Creation & Management
- Every client automatically gets a wallet upon created
- Client receives money from a bank (via webhook) and deposits the amount into his balance
- Handles Money deposits & withdrawals 
- Every financial operation recorded
- Complete audit trail of all wallet activity
- Atomic operations

### Payments
- Every client can initiate a payment request to transfer money
- Every payment request is recorded and gets updated once succeeded 
- Successful payment requests reflect to the client's wallet

### System Reliability
- Queue-based architecture to prevent data loss during processing pauses
- Signature verification for secure webhook authentication
- Transaction validation and error handling
- Atomic database operations to maintain data consistency

### Efficiency & Scalability

- The system is built to efficiently handle large volumes of transactions in a single webhook payload. It has been tested with high-density transaction data to ensure performance and scalability.
For example, the file ``` [unique_transactions_batch.txt](https://github.com/Amro404/online-wallet/blob/main/unique_transactions_batch.txt) ``` in the project root path contains 2,000 lines of raw transaction data, simulating a real-world high-volume webhook scenario. The application can parse, validate, and process all transactions from this file in a single request,

### Prerequisites
- PHP 8.1+
- Composer 2.0+
- MySQL
- Redis (for queue)

## 📦 Installation

1. Clone the repository
    ```
   git clone https://github.com/Amro404/online-wallet.git
   ```
2. Install composer dependencies:

    ```bash
    composer install
    ```

3. Create your configuration file `.env`:

    ```
    cp .env.example .env
    ```
4. Create Application key
    ```
    php artisan key:generate        
    ```
5. Configure the database connection

6. Run database migrations and seeders
    ```
    php artisan migrate --seed
    ```
7. Run the application in your preferred way, either it's `valet`, `serve`, or any other way.
    ```bash
    php artisan serve
    ```
8. Start the queue worker to handle coming webhooks
    ```
    php artisan queue:work
    ```

## 📡 Webhook Endpoints

This section describes the webhook endpoints supported by the application for bank transaction ingestion. These endpoints accept plain text payloads from external systems (e.g., Foodics, Acme) and parse them into structured transaction data.

---

### 🔗 POST `/webhooks/banks/foodics/transactions`

**Headers:**
```
Content-Type: text/plain
X-Signature: "123456"
X-Merchant-Id: "CLIENT-12345"
```

**Example Request Body:**

```
20250615156,50#202506159000001#note/debt payment march/internal_reference/A462JE81
```

**Success Response:**

```json
{
    "message": "Webhook received successfully"
}
```

### 🔗 POST `/webhooks/banks/acme/transactions`

**Headers:**
```
Content-Type: text/plain
X-Signature: "123456"
X-Merchant-Id: "CLIENT-12345"
```

**Example Request Body:**

```
2000,50//202506159000061//20250615
```

**Success Response:**

```json
{
    "message": "Webhook received successfully"
}
```

### In addition, any transactions that arrive while ingestion is paused are not lost. They are queued for later processing and can be resumed using the built-in Artisan command:
``` php artisan app:process-pending-webhooks```


## 📡 Payment Endpoint

### 🔗 POST `/api/v1/payments`

**Example Request Body:**

```json
{
    "client_id": "1",
    "amount": 1500.7,
    "currency": "SAR",
    "sender_account_number": "SA0380000000608010167519",
    "receiver_account_number": "SA0380000000608010167520",
    "receiver_bank_code": "FDCSSARI",
    "receiver_beneficiary_name": "Jane Doe",
    "payment_type": "421",
    "charge_details": "SHA",
    "notes": [
        "Invoice #2023-456",
        "Contract renewal payment"
    ]
}
```

## 📁 Project Structure

This project is organized into `Domain` and `Infrastructure` layers following DDD. Each subdomain encapsulates its logic, contracts, DTOs, services, repositories, and exceptions.

```
src/
├── Domain/
│   ├── Banking/
│   │   ├── Actions/
│   │   │   └── ProcessBankTransactionsAction.php
│   │   ├── Contracts/
│   │   │   └── BankWebhookParserInterface.php
│   │   ├── DataTransferObjects/
│   │   │   ├── BankTransaction.php
│   │   │   ├── BankWebhookPayload.php
│   │   │   └── RawTransactionWebhook.php
│   │   ├── Enums/
│   │   │   ├── BankType.php
│   │   │   └── RawTransactionWebhookStatus.php
│   │   ├── Factories/
│   │   │   └── BankWebhookParserFactory.php
│   │   ├── Repositories/
│   │   │   ├── BankTransactionRepositoryInterface.php
│   │   │   ├── RawTransactionWebhookRepositoryInterface.php
│   │   │   └── WebhookIngestionSettingRepositoryInterface.php
│   │   ├── Services/
│   │   │   ├── BankWebhookHandlerService.php
│   │   │   ├── Parsers/
│   │   │   │   ├── AcmeWebhookParser.php
│   │   │   │   └── FoodicsWebhookParser.php
│   │   │   ├── RawTransactionWebhookService.php
│   │   │   └── WebhookIngestionSettingService.php
│   │   └── ValueObjects/
│   │       └── Money.php
│   ├── Client/
│   │   ├── Repositories/
│   │   │   └── ClientRepositoryInterface.php
│   │   └── Services/
│   │       └── ClientService.php
│   ├── Payment/
│   │   ├── Contracts/
│   │   │   └── BankAdapterInterface.php
│   │   ├── DataTransferObjects/
│   │   │   ├── PaymentRequest.php
│   │   │   └── PaymentResponse.php
│   │   ├── Entities/
│   │   │   ├── ReceiverInfo.php
│   │   │   └── SenderInfo.php
│   │   ├── Enums/
│   │   │   └── PaymentStatus.php
│   │   ├── Events/
│   │   │   └── PaymentRequestCreated.php
│   │   ├── Factories/
│   │   │   └── PaymentRequestFactory.php
│   │   ├── Repositories/
│   │   │   └── PaymentRepositoryInterface.php
│   │   ├── Services/
│   │   │   ├── BankXAdapter.php
│   │   │   ├── PaymentRequestXmlGenerator.php
│   │   │   └── PaymentService.php
│   │   └── ValueObjects/
│   │       ├── Amount.php
│   │       ├── ChargeDetails.php
│   │       ├── PaymentType.php
│   │       └── Reference.php
│   └── Wallet/
│       ├── Contracts/
│       │   ├── WalletHolderInterface.php
│       │   └── WalletInterface.php
│       ├── DataTransferObjects/
│       │   └── WalletTransaction.php
│       ├── Enums/
│       │   ├── WalletTransactionStatus.php
│       │   └── WalletTransactionType.php
│       ├── Events/
│       │   └── WalletTransactionCreated.php
│       ├── Exceptions/
│       │   ├── AmountInvalidBaseException.php
│       │   ├── BalanceIsEmptyBaseException.php
│       │   ├── InsufficientFundsBaseException.php
│       │   ├── WalletBaseException.php
│       │   └── WalletNotFoundException.php
│       ├── Repositories/
│       │   ├── WalletRepositoryInterface.php
│       │   └── WalletTransactionRepositoryInterface.php
│       ├── Services/
│       │   ├── ConsistencyService.php
│       │   ├── LockService.php
│       │   ├── PrepareService.php
│       │   ├── WalletDomainService.php
│       │   ├── WalletService.php
│       │   └── WalletTransactionService.php
├── Infrastructure/
│   ├── Integration/
│   │   └── BankClient.php
│   └── Repositories/
│       ├── EloquentBankTransactionRepository.php
│       ├── EloquentClientRepository.php
│       ├── EloquentPaymentRepository.php
│       ├── EloquentRawTransactionWebhookRepository.php
│       ├── EloquentWalletRepository.php
│       ├── EloquentWalletTransactionRepository.php
│       └── EloquentWebhookIngestionSettingRepository.php

```

## 🧩 Domain Overview

### 🔹 `Banking`

Handles parsing and processing of incoming bank webhook transactions.

- **Actions**
    - `ProcessBankTransactionsAction` — Entry point for processing webhook transactions.

- **Contracts**
    - `BankWebhookParserInterface` — Interface for custom webhook parsers.

- **DTOs**
    - `BankTransaction`, `BankWebhookPayload`, `RawTransactionWebhook`

- **Enums**
    - `BankType`, `RawTransactionWebhookStatus`

- **Factories**
    - `BankWebhookParserFactory` — Returns the appropriate parser by bank type.

- **Repositories (Interfaces)**
    - For saving parsed transactions and webhook ingestion settings.

- **Services**
    - `BankWebhookHandlerService`, `RawTransactionWebhookService`, etc.
    - `Parsers/` — Individual webhook parsers like `FoodicsWebhookParser`, `AcmeWebhookParser`.

- **ValueObjects**
    - `Money`

---

### 🔹 `Client`

Manages client data.

- **Repositories**
- **Services**
    - `ClientService`

---

### 🔹 `Payment`

Responsible for outgoing payments and XML generation.

- **Contracts**
    - `BankAdapterInterface` — Adapter for communicating with banks.

- **DTOs**
    - `PaymentRequest`, `PaymentResponse`

- **Entities**
    - `SenderInfo`, `ReceiverInfo`

- **Events**
    - `PaymentRequestCreated`

- **Services**
    - `PaymentRequestXmlGenerator`, `PaymentService`, `BankXAdapter`

- **ValueObjects**
    - `Amount`, `PaymentType`, `Reference`, etc.

---

### 🔹 `Wallet`

Core wallet logic: balance tracking, transaction creation, and consistency checks.

- **Contracts**
    - `WalletInterface`, `WalletHolderInterface`

- **DTOs**
    - `WalletTransaction`

- **Events & Exceptions**
    - `WalletTransactionCreated`, custom exceptions

- **Repositories (Interfaces)**

- **Services**
    - `WalletService`, `WalletTransactionService`, `ConsistencyService`, etc.

---

## 🏗️ Infrastructure Layer

Implements persistence for domain repositories using Eloquent ORM.

- **Integration**
    - `BankClient` — HTTP client for bank APIs.

- **Repositories**
    - `Eloquent*Repository.php` implementations.

---

## Tests
To run all unit tests, use the following command:

``` php artisan test ```

---

## 📎 API Resources

### Postman Collection

You can test the API using [Online Wallet](https://bold-rocket-3659.postman.co/workspace/New-Team-Workspace~c2b35a23-b34a-4b15-88b7-51da95f55cac/collection/9044491-41c05fa5-14bc-472e-8cff-18f6acc9c17e?action=share&creator=9044491) Postman Collection


