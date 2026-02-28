DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `AEPSDisputeTxnJob`(IN `inUserId` BIGINT(20), IN `inAepsTransactionPKId` BIGINT(20), IN `inTxnId` VARCHAR(200), IN `inTxnRefId` VARCHAR(200), IN `inUtr` VARCHAR(100), IN `inTrTotalAmount` DOUBLE(18,2), IN `inTrAmount` DOUBLE(18,2), IN `inCommission` DOUBLE(18,2), IN `inTds` DOUBLE(18,2), IN `inTxnNarration` VARCHAR(200), IN `inServiceId` VARCHAR(50), IN `inOldTxnId` VARCHAR(50), IN `inAdminId` VARCHAR(20), OUT `outData` JSON)
BEGIN
DECLARE checkPkId BIGINT(20);
DECLARE aepsTransactionRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE messageOut VARCHAR(200);
SET AUTOCOMMIT = false;
START TRANSACTION;
SET flag = 0;
    SELECT `id` INTO checkPkId FROM `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = inTxnRefId LIMIT 1;
    SELECT `id` INTO aepsTransactionRowId FROM `aeps_transactions` WHERE `id` = inAepsTransactionPKId AND `is_trn_credited` = '1' AND `is_trn_disputed` = '0';
    IF(checkPkId IS NULL AND aepsTransactionRowId) THEN
        UPDATE `users` SET `transaction_amount` = (`transaction_amount` - inTrTotalAmount) WHERE `id` = inUserId;
        UPDATE `aeps_transactions` SET `is_trn_disputed` = '1', `trn_disputed_at` = NOW(), `status` = 'disputed', `updated_at` = NOW() WHERE `id` = aepsTransactionRowId;
        SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = inUserId;
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`,`tr_commission`,`tr_tds`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `udf1`, `udf2`, `created_at`, `updated_at`)
            VALUES (inTxnId, inTxnRefId, inUserId, inUtr, primaryAccountNumber, CONCAT('-', inTrTotalAmount), inTrAmount,inCommission, inTds,   'dr', NOW(), 'aeps_inward_dispute', inTxnNarration, primaryTxnAmount, inServiceId, inOldTxnId, inAdminId, NOW(), NOW());
        SET flag = 1;
    ELSE
    set messageOut = 'Transaction already debited OR UTR not found';
    END IF;
    IF(flag) THEN
        COMMIT;
        SET messageOut = 'Primary balance debited successfully';
    ELSE
        ROLLBACK;
        SET messageOut = 'Query Error';
        SET flag = 0;
    END IF;
    SELECT JSON_MERGE(JSON_OBJECT('ref_id', inUtr),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', messageOut)) INTO outData;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `AutoCollectCreditTransaction`(IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `identifier` VARCHAR(200), IN `serviceId` VARCHAR(200), OUT `outData` JSON)
BEGIN

DECLARE userId BIGINT(20);
DECLARE uniqueTxnNumber VARCHAR(200);
DECLARE txnRefId VARCHAR(200);
DECLARE fundCallbackTableId BIGINT(20) DEFAULT 0;
DECLARE userName VARCHAR(200);
DECLARE userEmail VARCHAR(200);
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE primaryClosingBalance DOUBLE(18,2);
DECLARE creditedAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

	SELECT `id`, `user_id`, `utr`, `reference_id` INTO fundCallbackTableId, userId, uniqueTxnNumber, txnRefId FROM `cf_merchants_fund_callbacks` WHERE `id` = rowId AND `is_trn_credited` = '0';
    
    IF(fundCallbackTableId) THEN
    	SELECT `name`, `email`, `account_number`, `transaction_amount` INTO userName, userEmail, primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
        
        IF(primaryAccountNumber) THEN
        
        	SET creditedAmount = txnAmount - txnFee - txnTax;
        	SET primaryClosingBalance = primaryTxnAmount + creditedAmount;
            
            INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `udf1`, `service_id`, `created_at`)
		VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, 'cr', NOW(), identifier, txnNarration, primaryClosingBalance, txnRefId, uniqueTxnNumber, serviceId, NOW());
        
        	UPDATE `users` SET `transaction_amount` = (`transaction_amount` + creditedAmount) WHERE `id` = userId;
        
        	UPDATE `cf_merchants_fund_callbacks` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = fundCallbackTableId;
            
            SET flag = 1;
        
        	IF(flag) THEN
        		COMMIT;
            	SET message = 'Primary balance credited successfully';
        	ELSE
        		ROLLBACK;
            	SET message = 'Query Error';
            	SET flag = 0;
        	END IF;
            
		END IF;
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', uniqueTxnNumber),
                      JSON_OBJECT('name', userName),
                      JSON_OBJECT('email', userEmail),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `AutoCollectDebitTransaction`(IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `identifier` VARCHAR(200), IN `txnType` VARCHAR(200), IN `serviceId` VARCHAR(200), OUT `outData` JSON)
BEGIN

DECLARE userId BIGINT(20);
DECLARE requestId VARCHAR(200);
DECLARE tableRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE primaryClosingBalance DOUBLE(18,2);
DECLARE drAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

	SELECT `id`, `user_id`, `request_id` INTO tableRowId, userId, requestId FROM `cf_merchants` WHERE `id` = rowId AND `is_fee_charged` = '0';
    
    IF(tableRowId) THEN
    	SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
        
        IF(primaryAccountNumber) THEN
        
        	SET drAmount = txnFee + txnTax;
        	SET primaryClosingBalance = primaryTxnAmount - drAmount;
            
            INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `created_at`)
		VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, txnType, NOW(), identifier, txnNarration, primaryClosingBalance, serviceId, NOW());
        
        	UPDATE `users` SET `transaction_amount` = (`transaction_amount` - drAmount) WHERE `id` = userId;
        
        	UPDATE `cf_merchants` SET `is_fee_charged` = '1', `fee_charged_at` = NOW(), `updated_at` = NOW() WHERE `id` = tableRowId;
            
            SET flag = 1;
        
        	IF(flag) THEN
        		COMMIT;
            	SET message = 'Fee and Tax debited successfully';
        	ELSE
        		ROLLBACK;
            	SET message = 'Query Error';
            	SET flag = 0;
        	END IF;
            
		END IF;
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', requestId),
                      JSON_OBJECT('message', message),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('status', flag)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `EbPartnerVanCreditTxnJob`(IN `inUserId` BIGINT(20), IN `inRowId` BIGINT(20), IN `inTxnId` VARCHAR(200), IN `inTxnReferenceId` VARCHAR(200), IN `inUtr` VARCHAR(100), IN `inTrTotalAmountSigned` VARCHAR(25), IN `inTrAmount` DOUBLE(18,2), IN `inTxnFee` DOUBLE(18,2), IN `inTxnTax` DOUBLE(18,2), IN `inCreditedAmt` DOUBLE(18,2), IN `inTxnNarration` TEXT, IN `inServiceId` VARCHAR(200), IN `inFeeRate` VARCHAR(100), IN `inIdentifire` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE ebCallbackTblId BIGINT(20) DEFAULT 0;
DECLARE transactionDate DATETIME;
DECLARE userName VARCHAR(200);
DECLARE userEmail VARCHAR(200);
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = inTxnReferenceId limit 1;
	  SELECT `id`, `payment_time` INTO ebCallbackTblId, transactionDate FROM `fund_receive_callbacks` WHERE `id` = inRowId AND `is_trn_credited` = '0';
    
    IF(txnPkId is NULL AND ebCallbackTblId) THEN
    	
        UPDATE `users` SET `transaction_amount` = (`transaction_amount` + inCreditedAmt) WHERE `id` = inUserId;
        
        UPDATE `fund_receive_callbacks` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = ebCallbackTblId;

        SELECT `name`, `email`, `account_number`, `transaction_amount` INTO userName, userEmail, primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = inUserId;
            
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `order_id`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
		    VALUES (inTxnId, inTxnReferenceId, inUserId, primaryAccountNumber, inTrTotalAmountSigned, inTrAmount, inTxnFee, inTxnTax, 'cr', transactionDate, inIdentifire, inTxnNarration, primaryTxnAmount, inUtr, inServiceId, inFeeRate, NOW(), NOW());

        SET flag = 1;
        
    ELSE
    
    	SET message = 'UTR not found';
    
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Primary balance credited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', inUtr),
                      JSON_OBJECT('name', userName),
                      JSON_OBJECT('email', userEmail),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `LeanMarkBalanceJob`(IN `inRowId` BIGINT(20), IN `inUserId` BIGINT(20), IN `inLeanAmount` DOUBLE(18,2), OUT `outData` JSON)
BEGIN

    DECLARE openingBalance BIGINT(20);
    DECLARE closingBalance BIGINT(20);
    DECLARE checkRowId BIGINT DEFAULT NULL;
    DECLARE checkStatus CHAR DEFAULT '1';
    DECLARE msg VARCHAR(100) DEFAULT 'init';
    DECLARE flag INT(1) DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET flag = 0;
        ROLLBACK;
    END;

    START TRANSACTION;

        SELECT `id`, `status` INTO checkRowId, checkStatus FROM `lean_mark_transactions` WHERE `id` = inRowId AND `user_id` = inUserId;

        IF (checkRowId IS NOT NULL AND checkStatus = '0') THEN

            UPDATE `users` SET `transaction_amount` = (@crrBal := `transaction_amount`) - inLeanAmount WHERE `id` = inUserId;

            SET openingBalance = @crrBal;

            SET closingBalance = openingBalance - inLeanAmount;

            UPDATE `lean_mark_transactions` SET 
                `opening_balance` = openingBalance,
                `closing_balance` = closingBalance,
                `status` = '1',
                `updated_at` = NOW()
                WHERE `id` = inRowId;

            SET msg = 'Everything done smoothly.';
            SET flag = 1;

        ELSE

            SET msg = 'checkRowId is remain null !';

        END IF;
        
    COMMIT;
    
    SELECT JSON_MERGE(JSON_OBJECT('status', flag),
                    JSON_OBJECT('ob', openingBalance),
                    JSON_OBJECT('cb', closingBalance),
                    JSON_OBJECT('msg', msg),
                    JSON_OBJECT('checkRowId', checkRowId),
                    JSON_OBJECT('checkStatus', checkStatus),
                    JSON_OBJECT('date', NOW())) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `LoadMoneyFundCreditTransaction`(IN `serviceId` VARCHAR(100), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `remarks` TEXT, IN `adminId` BIGINT(20), OUT `outData` JSON)
BEGIN

DECLARE userId BIGINT(20);
DECLARE utRef VARCHAR(200);
DECLARE requestId VARCHAR(200);
DECLARE fundCallbackTableId BIGINT(20) DEFAULT 0;
DECLARE userName VARCHAR(200);
DECLARE userEmail VARCHAR(200);
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE primaryClosingBalance DOUBLE(18,2);
DECLARE creditedAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

	SELECT `id`, `user_id`, `utr`, `request_id` INTO fundCallbackTableId, userId, utRef, requestId FROM `load_money_request` WHERE `id` = rowId AND `is_trn_credited` = '0' AND `status` = 'pending';
    
    IF(fundCallbackTableId) THEN
    	SELECT `name`, `email`, `account_number`, `transaction_amount` INTO userName, userEmail, primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
        
        IF(primaryAccountNumber) THEN
        
        	SET creditedAmount = txnAmount - txnFee - txnTax;
        	SET primaryClosingBalance = primaryTxnAmount + creditedAmount;
            
            
            INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `service_id`, `created_at`, `updated_at`)
		VALUES (txnId, requestId, userId, utRef, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, 'cr', NOW(), 'load_fund_credit', txnNarration, primaryClosingBalance, utRef, serviceId, NOW(), NOW());
        
        	UPDATE `users` SET `transaction_amount` = (`transaction_amount` + creditedAmount) WHERE `id` = userId;
        
        	UPDATE `load_money_request` SET `txn_id` = txnId, `admin_id` = adminId, `remarks` = remarks, `is_trn_credited` = '1', `status` = 'approved', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = fundCallbackTableId;
            
            SET flag = 1;
        
        	IF(flag) THEN
        		COMMIT;
            	SET message = 'Primary balance credited successfully';
        	ELSE
        		ROLLBACK;
            	SET message = 'Query Error';
            	SET flag = 0;
        	END IF;
            
		END IF;
        
    ELSE
    
    	SET message = 'UTR not found';
    
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', utRef),
                      JSON_OBJECT('name', userName),
                      JSON_OBJECT('email', userEmail),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `LoadMoneyFundCreditTxnJob`(IN `inUserId` BIGINT(20), IN `serviceId` VARCHAR(100), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `remarks` TEXT, IN `adminId` BIGINT(20), IN `feeRate` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE utRef VARCHAR(200);
DECLARE requestId VARCHAR(200);
DECLARE fundCallbackTableId BIGINT(20) DEFAULT 0;
DECLARE userName VARCHAR(200);
DECLARE userEmail VARCHAR(200);
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE creditedAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = requestId limit 1;
	SELECT `id`, `user_id`, `utr`, `request_id` INTO fundCallbackTableId, userId, utRef, requestId FROM `load_money_request` WHERE `id` = rowId AND `is_trn_credited` = '0' AND `status` = 'pending';
    
    IF(txnPkId is NULL AND fundCallbackTableId) THEN

        SET creditedAmount = txnAmount - txnFee - txnTax;

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` + creditedAmount) WHERE `id` = userId;
        
        UPDATE `load_money_request` SET `txn_id` = txnId, `admin_id` = adminId, `remarks` = remarks, `is_trn_credited` = '1', `status` = 'approved', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = fundCallbackTableId;

    	SELECT `name`, `email`, `account_number`, `transaction_amount` INTO userName, userEmail, primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;            
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
	        VALUES (txnId, requestId, userId, utRef, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, 'cr', NOW(), 'load_fund_credit', txnNarration, primaryTxnAmount, utRef, serviceId, feeRate, NOW(), NOW());
        	
        SET flag = 1;
        
    ELSE
    
    	SET message = 'UTR not found';
    
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Primary balance credited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', utRef),
                      JSON_OBJECT('name', userName),
                      JSON_OBJECT('email', userEmail),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `LoadMoneyFundTransfer`(IN `inUserId` BIGINT(20), IN `serviceId` VARCHAR(100), IN `txnId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `remarks` TEXT, IN `adminId` BIGINT(20), IN `feeRate` VARCHAR(100), IN `txnType` VARCHAR(25), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE userName VARCHAR(200);
DECLARE userEmail VARCHAR(200);
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE creditedAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE primaryOpeningAmount VARCHAR(100);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_id` = txnId limit 1;
	
    SELECT `transaction_amount` INTO primaryOpeningAmount FROM `users` WHERE `id` = inUserId; 
    IF(txnPkId is NULL) THEN
		

        SET creditedAmount = txnAmount - txnFee - txnTax;
		IF (txnType = 'cr') THEN
			UPDATE `users` SET `transaction_amount` = (`transaction_amount` + creditedAmount) WHERE `id` = inUserId;
		ELSE 
			UPDATE `users` SET `transaction_amount` = (`transaction_amount` - creditedAmount) WHERE `id` = inUserId;
        END IF;

    	SELECT `name`, `email`, `account_number`, `transaction_amount` INTO userName, userEmail, primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = inUserId;            
            
        INSERT INTO `transactions`(`txn_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`,`opening_balance`, `closing_balance`,`service_id`, `fee_rate`, `created_at`, `updated_at`)
	        VALUES (txnId, inUserId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, txnType, NOW(), 'load_fund_credit', txnNarration,primaryOpeningAmount, primaryTxnAmount, serviceId, feeRate, NOW(), NOW());
        	
        SET flag = 1;
      ELSE
    
    	SET message = 'UTR not found';
    
    END IF;  
    

    IF(flag) THEN
        COMMIT;
        SET message = 'Primary balance credited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('name', userName),
                      JSON_OBJECT('email', userEmail),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `OrderStatusProcessedUpdate`(IN `orderRefId` VARCHAR(250), IN `userId` INT, IN `ordStatus` VARCHAR(50), IN `responseMessage` VARCHAR(255), IN `statusCode` VARCHAR(50), IN `bankRef` VARCHAR(100), OUT `data` JSON)
BEGIN
DECLARE srv_id VARCHAR(200);
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE pk_id INT DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE flag VARCHAR(25) DEFAULT 0;
	SET AUTOCOMMIT = 0;
	START TRANSACTION;
    set flag = 0;
	IF ordStatus = 'processed' THEN
		SELECT `amount`, `fee`, `tax`, `id`, `service_id` INTO ord_amount, ord_fee, ord_tax, pk_id, srv_id
			FROM `orders`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = 'processing' AND `cron_status` = '1';
        IF (pk_id != '') THEN
			UPDATE `orders` SET `status` = ordStatus, status_code = statusCode , bank_reference = bankRef, status_response = responseMessage, updated_at = NOW(), trn_reflected = '1' , trn_reflected_at =  NOW() WHERE   `id` = pk_id;
			set flag = 1;
            IF (flag) THEN
                COMMIT;
                set message = 'Order processed successfully';
            ELSE
                ROLLBACK;
                set message = 'Query Error';
            END IF;
        ELSE
		  ROLLBACK;
        set message = 'Allready amount processed';
		
        END IF;
	END IF;
	SET AUTOCOMMIT = 1;
	SELECT JSON_MERGE(JSON_OBJECT('status', flag), JSON_OBJECT( 'message', message )) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `OrderStatusUpdate`(IN `orderRefId` VARCHAR(250), IN `userId` INT, IN `serviceId` INT, IN `ordStatus` VARCHAR(50), IN `txnId` VARCHAR(50), IN `errorDescription` VARCHAR(255), IN `statusCode` VARCHAR(50), IN `utr` VARCHAR(200), OUT `data` JSON)
BEGIN
DECLARE srv_id INT DEFAULT 0;
DECLARE srv_acc_number VARCHAR(150) DEFAULT NULL;
DECLARE ord_service_id VARCHAR(250) ;
DECLARE srv_txn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE pk_id INT DEFAULT 0;
DECLARE totalAmount  DOUBLE(18, 2) DEFAULT 0;
DECLARE ser_transaction_amount  DOUBLE(18, 2) DEFAULT 0;
DECLARE ser_old_balance  DOUBLE(18, 2) DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE tr_narration_str varchar(200); 
	SET AUTOCOMMIT = 0;

	START TRANSACTION;
    set flag = 0;
	IF ordStatus = 'failed' THEN
		SELECT `amount`, `fee`, `tax`, `id`, `service_id` INTO ord_amount, ord_fee, ord_tax, pk_id, ord_service_id
			FROM `orders`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = 'processing' AND `cron_status` = '1';
	set totalAmount = ROUND(ord_amount + ord_fee + ord_tax, 2);	
        IF (pk_id != '') THEN
 
            SELECT `id`, `transaction_amount` INTO srv_id, ser_transaction_amount
                FROM `user_services`
                WHERE `id` = serviceId;
		
		set ser_old_balance = ser_transaction_amount;
               
		if(srv_id != '') THEN
                    set tr_narration_str = CONCAT(totalAmount , ' credited against ', orderRefId);
                    
		 SELECT `id`, `service_account_number`, `transaction_amount` INTO srv_id, srv_acc_number, srv_txn_amount
                FROM `user_services`
                WHERE `id` = serviceId;
					INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_fee`, `tr_tax`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`,`opening_balance`, `closing_balance`, `service_id`, `created_at`)
                      VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, ord_amount, CONCAT('+',totalAmount) , ord_fee, ord_tax, NOW(), 'cr', 'payout_refund', tr_narration_str  , utr,ser_old_balance,(SELECT  `transaction_amount` 
                FROM `user_services`
                WHERE `id` = serviceId ) + totalAmount, ord_service_id, NOW());
			
			UPDATE user_services SET transaction_amount   = transaction_amount + totalAmount   WHERE  `id` = serviceId;
		
			UPDATE `orders` SET `status` = ordStatus, status_code = statusCode, failed_status_code = statusCode, failed_message = errorDescription, updated_at = NOW(), trn_loc_refunded = '1' , trn_loc_refunded_at =  NOW() WHERE  `order_ref_id` = orderRefId AND `id` = pk_id;
			set flag = 1;
                      IF (flag) THEN
					    SELECT `transaction_amount` INTO  ser_transaction_amount
									FROM `user_services`
									WHERE `id` = serviceId;
					  
					  	IF (ser_old_balance = (ser_transaction_amount - totalAmount)) THEN 
					
						   COMMIT;
							set message = 'Payment balance Credited successfully';
						ELSE
							ROLLBACK;
							set flag = 0;
							set message = 'DB Query Error';
						END IF;
                     ELSE
						ROLLBACK;
							set flag = 0;
                        set message = 'Query Error';
                    END IF;
                 ELSE
					set flag = 0;
					set message = CONCAT('User Service account not found ' );

                END IF;


        ELSE
		
        set message = 'Allready amount refunded';
		
        END IF;
	END IF;
	
	IF ordStatus = 'reversed' THEN
		SELECT `amount`, `fee`, `tax`, `id`, `service_id` INTO ord_amount, ord_fee, ord_tax, pk_id, ord_service_id
			FROM `orders`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = 'processed' AND `cron_status` = '1';
	set totalAmount = ROUND(ord_amount + ord_fee + ord_tax, 2);	
        IF (pk_id != '') THEN
 
            SELECT `id`, `transaction_amount` INTO srv_id, ser_transaction_amount
                FROM `user_services`
                WHERE `id` = serviceId;
					set ser_old_balance = ser_transaction_amount;
               
		if(srv_id != '') THEN
                    set tr_narration_str = CONCAT(totalAmount , ' credited against ', orderRefId);
                      SELECT `id`, `service_account_number`, `transaction_amount` INTO srv_id, srv_acc_number, srv_txn_amount
                FROM `user_services`
                WHERE `id` = serviceId;
                     INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_fee`, `tr_tax`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`,`opening_balance`, `closing_balance`, `service_id`, `created_at`)
                      VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, ord_amount, CONCAT('+',totalAmount) , ord_fee, ord_tax, NOW(), 'cr', 'payout_reversed', tr_narration_str  , utr,ser_old_balance,(SELECT  `transaction_amount` 
                FROM `user_services`
                WHERE `id` = serviceId ) + totalAmount, ord_service_id, NOW());
			
			UPDATE user_services SET transaction_amount   = transaction_amount + totalAmount    WHERE  `id` = serviceId;
		
			UPDATE `orders` SET `status` = ordStatus, status_code = statusCode, failed_status_code = statusCode, failed_message = errorDescription, updated_at = NOW(), trn_loc_refunded = '1' , trn_loc_refunded_at =  NOW() WHERE  `order_ref_id` = orderRefId AND `id` = pk_id;
			set flag = 1;
                      IF (flag) THEN
					  
					     SELECT `transaction_amount` INTO  ser_transaction_amount
									FROM `user_services`
									WHERE `id` = serviceId;
					  
					  	IF (ser_old_balance = (ser_transaction_amount - totalAmount)) THEN 
						   COMMIT;
							set message = 'Payment balance Credited successfully';
						ELSE
							ROLLBACK;
							set flag = 0;
							set message = 'DB Query Error';
						END IF;
                     ELSE
						ROLLBACK;
						set flag = 0;
                        set message = 'Query Error';
                    END IF;
                 ELSE
              
                 set message = CONCAT('User Service account not found ' );

                END IF;


        ELSE
		
        set message = 'Allready amount refunded';
		
        END IF;
	END IF;
    
    IF ordStatus = 'processed' THEN
		SELECT `amount`, `fee`, `tax`, `id`, `service_id` INTO ord_amount, ord_fee, ord_tax, pk_id, ord_service_id
			FROM `orders`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = 'failed' AND `cron_status` = '1';
	set totalAmount = ROUND(ord_amount + ord_fee + ord_tax, 2);	
        IF (pk_id != '') THEN
 
            SELECT `id`, `transaction_amount` INTO srv_id, ser_transaction_amount
                FROM `user_services`
                WHERE `id` = serviceId;
					set ser_old_balance = ser_transaction_amount;
               
		if(srv_id != '') THEN
                    set tr_narration_str = CONCAT(totalAmount , ' debited against ', orderRefId);
                      SELECT `id`, `service_account_number`, `transaction_amount` INTO srv_id, srv_acc_number, srv_txn_amount
                FROM `user_services`
                WHERE `id` = serviceId;
                     INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_fee`, `tr_tax`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`,`opening_balance`, `closing_balance`, `service_id`, `created_at`)
                      VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, ord_amount, CONCAT('-',totalAmount) , ord_fee, ord_tax, NOW(), 'dr', 'payout_disbursement', tr_narration_str  , utr,ser_old_balance,(SELECT  `transaction_amount` 
                FROM `user_services`
                WHERE `id` = serviceId ) - totalAmount, ord_service_id, NOW());
			
			UPDATE user_services SET transaction_amount   = transaction_amount - totalAmount    WHERE  `id` = serviceId;
		
			UPDATE `orders` SET `status` = ordStatus, bank_reference = utr, status_code = statusCode, failed_status_code = statusCode, failed_message = errorDescription, updated_at = NOW(), trn_loc_refunded = '1' , trn_loc_refunded_at =  NOW() WHERE  `order_ref_id` = orderRefId AND `id` = pk_id;
			set flag = 1;
                      IF (flag) THEN
					  
					     SELECT `transaction_amount` INTO  ser_transaction_amount
									FROM `user_services`
									WHERE `id` = serviceId;
					  
					  	IF (ser_old_balance = (ser_transaction_amount + totalAmount)) THEN 
						   COMMIT;
							set message = 'Payment balance Credited successfully';
						ELSE
							ROLLBACK;
							set flag = 0;
							set message = 'DB Query Error';
						END IF;
                     ELSE
						ROLLBACK;
						set flag = 0;
                        set message = 'Query Error';
                    END IF;
                 ELSE
              
                 set message = CONCAT('User Service account not found ' );

                END IF;


        ELSE
		
        set message = 'Allready amount refunded';
		
        END IF;
	END IF;
	 SET AUTOCOMMIT = true;
	SELECT JSON_MERGE(JSON_OBJECT('status', flag), JSON_OBJECT( 'message', message )) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `PartnerVanCreditTxnJob`(IN `inUserId` BIGINT(20), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `serviceId` VARCHAR(200), IN `feeRate` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE uniqueTxnNumber VARCHAR(200);
DECLARE txnRefId VARCHAR(200);
DECLARE fundCallbackTableId BIGINT(20) DEFAULT 0;
DECLARE userName VARCHAR(200);
DECLARE userEmail VARCHAR(200);
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE creditedAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = txnReferenceId limit 1;
	SELECT `id`, `user_id`, `utr`, `reference_id` INTO fundCallbackTableId, userId, uniqueTxnNumber, txnRefId FROM `fund_receive_callbacks` WHERE `id` = rowId AND `is_trn_credited` = '0';
    
    IF(txnPkId is NULL AND fundCallbackTableId) THEN

        SET creditedAmount = txnAmount - txnFee - txnTax;
    	
        UPDATE `users` SET `transaction_amount` = (`transaction_amount` + creditedAmount) WHERE `id` = userId;
        
        UPDATE `fund_receive_callbacks` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = fundCallbackTableId;

        SELECT `name`, `email`, `account_number`, `transaction_amount` INTO userName, userEmail, primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
            
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `order_id`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
		    VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, 'cr', NOW(), 'van_inward_credit', txnNarration, primaryTxnAmount, txnRefId, uniqueTxnNumber, serviceId, feeRate, NOW(), NOW());
            
        SET flag = 1;
        
    ELSE
    
    	SET message = 'UTR not found';
    
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Primary balance credited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', uniqueTxnNumber),
                      JSON_OBJECT('name', userName),
                      JSON_OBJECT('email', userEmail),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessedOrderStatusUpdate`(IN `orderRefId` VARCHAR(250), IN `userId` INT, IN `ordStatus` VARCHAR(50), IN `responseMessage` VARCHAR(255), IN `statusCode` VARCHAR(50), IN `bankRef` VARCHAR(100), IN `payoutId` VARCHAR(100), OUT `data` JSON)
BEGIN
DECLARE srv_id VARCHAR(200);
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE pk_id INT DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE flag VARCHAR(25) DEFAULT 0;
	SET AUTOCOMMIT = 0;
	START TRANSACTION;
    set flag = 0;
	IF ordStatus = 'processed' THEN
		SELECT `amount`, `fee`, `tax`, `id`, `service_id` INTO ord_amount, ord_fee, ord_tax, pk_id, srv_id
			FROM `orders`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = 'processed' AND `cron_status` = '1' and (bank_reference ='' or bank_reference is NULL);
        IF (pk_id != '') THEN
			UPDATE `orders` SET `status` = ordStatus, status_code = statusCode, payout_id = payoutId , bank_reference = bankRef, status_response = responseMessage, updated_at = NOW(), trn_reflected = '1' , trn_reflected_at =  NOW() WHERE   `id` = pk_id;
			set flag = 1;
            IF (flag) THEN
                COMMIT;
                set message = 'Order processed successfully';
            ELSE
                ROLLBACK;
                set message = 'Query Error';
            END IF;
        ELSE
		  ROLLBACK;
        set message = 'Allready amount processed';
		
        END IF;
	END IF;
	SET AUTOCOMMIT = 1;
	SELECT JSON_MERGE(JSON_OBJECT('status', flag), JSON_OBJECT( 'message', message )) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `RazPayPartnerVanCreditTxnJob`(IN `inUserId` BIGINT(20), IN `inRowId` BIGINT(20), IN `inTxnId` VARCHAR(200), IN `inTxnReferenceId` VARCHAR(200), IN `inUtr` VARCHAR(100), IN `inTrTotalAmountSigned` VARCHAR(25), IN `inTrAmount` DOUBLE(18,2), IN `inTxnFee` DOUBLE(18,2), IN `inTxnTax` DOUBLE(18,2), IN `inCreditedAmt` DOUBLE(18,2), IN `inTxnNarration` TEXT, IN `inServiceId` VARCHAR(200), IN `inFeeRate` VARCHAR(100), IN `inIdentifire` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE ebCallbackTblId BIGINT(20) DEFAULT 0;
DECLARE transactionDate DATETIME;
DECLARE userName VARCHAR(200);
DECLARE userEmail VARCHAR(200);
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = inTxnReferenceId limit 1;
	SELECT `id`, `payment_time` INTO ebCallbackTblId, transactionDate FROM `fund_receive_callbacks` WHERE `id` = inRowId AND `is_trn_credited` = '0';
    
    IF(txnPkId is NULL AND ebCallbackTblId) THEN
    	
        UPDATE `users` SET `transaction_amount` = (`transaction_amount` + inCreditedAmt) WHERE `id` = inUserId;
        
        UPDATE `fund_receive_callbacks` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = ebCallbackTblId;

        SELECT `name`, `email`, `account_number`, `transaction_amount` INTO userName, userEmail, primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = inUserId;
            
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `order_id`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
		    VALUES (inTxnId, inTxnReferenceId, inUserId, primaryAccountNumber, inTrTotalAmountSigned, inTrAmount, inTxnFee, inTxnTax, 'cr', transactionDate, inIdentifire, inTxnNarration, primaryTxnAmount, inUtr, inServiceId, inFeeRate, NOW(), NOW());

        SET flag = 1;
        
    ELSE
    
    	SET message = 'UTR not found';
    
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Primary balance credited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', inUtr),
                      JSON_OBJECT('name', userName),
                      JSON_OBJECT('email', userEmail),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SettlementStatusUpdate`(IN `orderRefId` VARCHAR(250), IN `txnId` VARCHAR(250), IN `userId` INT, IN `ordStatus` VARCHAR(50), IN `responseMessage` VARCHAR(255), IN `statusCode` VARCHAR(50), IN `failedMessage` VARCHAR(255), IN `bankRef` VARCHAR(250), OUT `data` JSON)
BEGIN
DECLARE pk_id INT DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE total_amount DOUBLE(18, 2);
DECLARE flag VARCHAR(25) DEFAULT 0;

	SET AUTOCOMMIT = 0;

	START TRANSACTION;
    set flag = 0;
	IF (ordStatus = 'processed' OR  ordStatus = 'failed') THEN
		SELECT  `id` INTO  pk_id
			FROM `user_settlements`
			WHERE `user_id` = userId AND `settlement_ref_id` = orderRefId AND `status` = 'processing';
        IF (pk_id != '') THEN
            UPDATE `user_settlements` SET `status` = ordStatus, `updated_at` = NOW() WHERE `user_id` = userId AND `settlement_ref_id` = orderRefId AND `status` = 'processing';
	UPDATE `user_settlement_logs` SET `status` = ordStatus, 
                `status_code` = statusCode , `bank_reference` = bankRef, `status_response` = responseMessage,
                `failed_message` = failedMessage, `updated_at` = NOW() WHERE `user_id` = userId AND `settlement_txn_id` = txnId AND `status` = 'processing';
		set flag = 1;
            IF (flag) THEN
                COMMIT;
                set message = 'Order status changed successfully';
            ELSE
                ROLLBACK;
                set message = 'Query Error';
            END IF;
        ELSE
		
        set message = 'Allready Order status changed';
		
        END IF;
	END IF;
	SELECT JSON_MERGE(JSON_OBJECT('status', flag), JSON_OBJECT( 'message', message )) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SmartCollectCreditTxnJob`(IN `inUserId` BIGINT(20), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `identifier` VARCHAR(200), IN `serviceId` VARCHAR(200), IN `feeRate` VARCHAR(100), IN `inFrequency` VARCHAR(50), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE uniqueTxnNumber VARCHAR(200);
DECLARE txnRefId VARCHAR(200);
DECLARE fundCallbackTableId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE creditedAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = txnReferenceId limit 1;
    SELECT `id`, `user_id`, `utr`, `reference_id` INTO fundCallbackTableId, userId, uniqueTxnNumber, txnRefId FROM `cf_merchants_fund_callbacks` WHERE `id` = rowId AND `is_trn_credited` = '0';
    
    IF(txnPkId is NULL AND fundCallbackTableId) THEN

        SET creditedAmount = txnAmount - txnFee - txnTax;

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` + creditedAmount) WHERE `id` = userId;
        UPDATE `cf_merchants_fund_callbacks` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = fundCallbackTableId;

        SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
        
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `order_id`, `service_id`, `fee_rate`, `udf2`, `created_at`, `updated_at`)
            VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, 'cr', NOW(), identifier, txnNarration, primaryTxnAmount, txnRefId, uniqueTxnNumber, serviceId, feeRate, inFrequency, NOW(), NOW());
        
        SET flag = 1;
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Primary balance credited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', uniqueTxnNumber),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SmartCollectDebitTxnJob`(IN `inUserId` BIGINT(20), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `identifier` VARCHAR(200), IN `txnType` VARCHAR(200), IN `serviceId` VARCHAR(200), IN `feeRate` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE requestId VARCHAR(200);
DECLARE tableRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE drAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = txnReferenceId limit 1;
	SELECT `id`, `user_id`, `request_id` INTO tableRowId, userId, requestId FROM `cf_merchants` WHERE `id` = rowId AND `is_fee_charged` = '0';
    
    IF(txnPkId is NULL AND tableRowId) THEN
    	
        SET drAmount = txnFee + txnTax;

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` - drAmount) WHERE `id` = userId;
        UPDATE `cf_merchants` SET `is_fee_charged` = '1', `fee_charged_at` = NOW(), `updated_at` = NOW() WHERE `id` = tableRowId;
    
        SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
        
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
            VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, txnType, NOW(), identifier, txnNarration, primaryTxnAmount, serviceId, feeRate, NOW(), NOW());
        
        SET flag = 1;            
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;


    IF(flag) THEN
        COMMIT;
        SET message = 'Fee and Tax debited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', requestId),
                      JSON_OBJECT('message', message),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('status', flag)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SmartCollectDisputeTxnJob`(IN `inUserId` BIGINT(20), IN `inUpiCallbackId` BIGINT(20), IN `inTxnId` VARCHAR(200), IN `inTxnRefId` VARCHAR(200), IN `inUtr` VARCHAR(100), IN `inTrTotalAmount` DOUBLE(18,2), IN `inTrAmount` DOUBLE(18,2), IN `inTxnFee` DOUBLE(18,2), IN `inTxnTax` DOUBLE(18,2), IN `inTxnNarration` VARCHAR(200), IN `inServiceId` VARCHAR(50), IN `inOldTxnId` VARCHAR(50), IN `inAdminId` VARCHAR(20), IN `inTrIdentifiers` VARCHAR(50), OUT `outData` JSON)
BEGIN

DECLARE checkPkId BIGINT(20);
DECLARE upiCallbacksRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE messageOut VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;
SET flag = 0;

    SELECT `id` INTO checkPkId FROM `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = inTxnRefId LIMIT 1;
    SELECT `id` INTO upiCallbacksRowId FROM `cf_merchants_fund_callbacks` WHERE `id` = inUpiCallbackId AND `is_trn_credited` = '1' AND `is_trn_disputed` = '0';

    IF(checkPkId IS NULL AND upiCallbacksRowId) THEN

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` - inTrTotalAmount) WHERE `id` = inUserId;
        UPDATE `cf_merchants_fund_callbacks` SET `is_trn_disputed` = '1', `trn_disputed_at` = NOW(), `updated_at` = NOW() WHERE `id` = upiCallbacksRowId;

        SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = inUserId;
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `udf1`, `udf2`, `created_at`, `updated_at`)
            VALUES (inTxnId, inTxnRefId, inUserId, inUtr, primaryAccountNumber, CONCAT('-', inTrTotalAmount), inTrAmount, inTxnFee, inTxnTax, 'dr', NOW(), inTrIdentifiers, inTxnNarration, primaryTxnAmount, inServiceId, inOldTxnId, inAdminId, NOW(), NOW());
        
        SET flag = 1;
    
    ELSE 
    set messageOut = 'Transaction already debited OR UTR not found';
    END IF;

    IF(flag) THEN
        COMMIT;
        SET messageOut = 'Primary balance debited successfully';
    ELSE
        ROLLBACK;
        SET messageOut = 'Query Error';
        SET flag = 0;
    END IF;
    SELECT JSON_MERGE(JSON_OBJECT('ref_id', inUtr),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', messageOut)) INTO outData;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `SmartCollectUpiFundSettleTxnJob`(IN `inTimestamp` VARCHAR(25), IN `inUserId` BIGINT(20), IN `inBatchId` VARCHAR(55), IN `inRowCounts` INT(10), IN `inServiceId` VARCHAR(55), IN `inTxnId` VARCHAR(55), IN `inSignedAfterFeeTaxAmount` VARCHAR(25), IN `inAfterFeeTaxAmount` DOUBLE(18,2), IN `inTrAmount` DOUBLE(18,2), IN `inFee` DOUBLE(18,2), IN `inTax` DOUBLE(18,2), IN `inTxnNarration` TEXT, IN `inFrequency` VARCHAR(55), OUT `outData` JSON)
BEGIN

DECLARE txnId VARCHAR(50) DEFAULT NULL;
DECLARE batchId VARCHAR(55) DEFAULT NULL;
DECLARE accountNumber VARCHAR(100);
DECLARE transactionAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;
SET flag = 0;


    SELECT `batch_id` INTO batchId FROM `cf_merchants_fund_callbacks`
        WHERE `user_id` = inUserId AND `batch_id` = inBatchId AND `txn_id` = inTxnId LIMIT 1;

    SELECT `txn_id` INTO txnId FROM `transactions`
        WHERE `user_id` = inUserId AND `txn_ref_id` = inBatchId AND `tr_reference` = inTimestamp LIMIT 1;
    
    
    IF(txnId IS NULL AND batchId IS NOT NULL) THEN

        UPDATE `cf_merchants_fund_callbacks` SET `is_trn_credited` = '1', `is_trn_settle` = '1', `trn_credited_at` = NOW(), `trn_settled_at` = NOW(), `updated_at` = NOW() 
            WHERE `user_id` = inUserId AND `batch_id` = inBatchId AND `txn_id` = inTxnId;

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` + inAfterFeeTaxAmount)
            WHERE `id` = inUserId;

        SELECT `account_number`, `transaction_amount` INTO accountNumber, transactionAmount FROM `users`
            WHERE `id` = inUserId;
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `service_id`, `udf1`, `udf2`, `created_at`, `updated_at`)
            VALUES (inTxnId, inBatchId, inUserId, inBatchId, accountNumber, inSignedAfterFeeTaxAmount, inTrAmount, inFee, inTax, 'cr', NOW(), 'smart_collect_vpa', inTxnNarration, transactionAmount, inTimestamp, inServiceId, inRowCounts, inFrequency, NOW(), NOW());
        
        SET flag = 1;
    
    ELSE 
        SET message = 'Smart Collect balance already settled OR batch ID not found';
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Smart Collect balance settled successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;

    SELECT JSON_MERGE(JSON_OBJECT('ref_id', inTxnId),
                      JSON_OBJECT('batch_id', inBatchId),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('user_id', inUserId),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiCollectCreditTransaction`(IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnNarration` TEXT, OUT `outData` JSON)
BEGIN

DECLARE customerRefId VARCHAR(200);
DECLARE userId BIGINT(20);
DECLARE merchantTxnRefId VARCHAR(200);
DECLARE npciTxnId VARCHAR(200);
DECLARE originalOrderId VARCHAR(200);
DECLARE payerVpa VARCHAR(200);
DECLARE payerAccName VARCHAR(200);
DECLARE upiCallbacksRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE primaryClosingBalance DOUBLE(18,2);
DECLARE creditedAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

	SELECT `id`, `user_id`, `customer_ref_id`, `npci_txn_id`, `original_order_id`, `payer_vpa`, `payer_acc_name`, `merchant_txn_ref_id` INTO upiCallbacksRowId, userId, customerRefId, npciTxnId, originalOrderId, payerVpa, payerAccName, merchantTxnRefId FROM `upi_collects` WHERE `id` = rowId AND `is_trn_credited` = '0';
    
    IF(upiCallbacksRowId) THEN
    	SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
        
        IF(primaryAccountNumber) THEN
        
        	SET creditedAmount = txnAmount;
        	SET primaryClosingBalance = primaryTxnAmount + creditedAmount;
            
            
            INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `udf1`, `udf2`, `udf3`, `udf4`, `created_at`)
		VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, 0, 0, 'cr', NOW(), 'upi_inward_credit', txnNarration, primaryClosingBalance, merchantTxnRefId, npciTxnId, originalOrderId, payerVpa, payerAccName, NOW());
        
        	UPDATE `users` SET `transaction_amount` = (`transaction_amount` + creditedAmount) WHERE `id` = userId;
        
        	UPDATE `upi_collects` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = upiCallbacksRowId;
            
            SET flag = 1;
        
        	IF(flag) THEN
        		COMMIT;
            	SET message = 'Primary balance credited successfully';
        	ELSE
        		ROLLBACK;
            	SET message = 'Query Error';
            	SET flag = 0;
        	END IF;
            
		END IF;
        
    ELSE
    
    	SET message = 'UTR not found';
    
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('ref_id', customerRefId),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiStackCreditTransaction`(IN `inUserId` BIGINT(20), IN `inCustomerRefId` VARCHAR(100), IN `serviceId` VARCHAR(100), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnAfterFeeTaxAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `feeRate` VARCHAR(100), OUT `outData` JSON)
BEGIN
DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE merchantTxnRefId VARCHAR(200);
DECLARE npciTxnId VARCHAR(200);
DECLARE originalOrderId VARCHAR(200);
DECLARE payerVpa VARCHAR(200);
DECLARE payerAccName VARCHAR(200);
DECLARE upiCallbacksRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE primaryClosingBalance DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;
SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `order_id` = inCustomerRefId limit 1;

    IF(txnPkId is NULL) THEN

	    SELECT `id`, `user_id`, `npci_txn_id`, `original_order_id`, `payer_vpa`, `payer_acc_name`, `merchant_txn_ref_id` INTO upiCallbacksRowId, userId, npciTxnId, originalOrderId, payerVpa, payerAccName, merchantTxnRefId FROM `upi_callbacks` WHERE `id` = rowId AND `is_trn_credited` = '0';

        IF(upiCallbacksRowId) THEN
            SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
            IF(primaryAccountNumber) THEN
                SET primaryClosingBalance = primaryTxnAmount + txnAmount;
                INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `service_id`, `udf1`, `udf2`, `udf3`, `udf4`, `fee_rate`, `created_at`, `updated_at`)
            VALUES (txnId, txnReferenceId, userId, inCustomerRefId, primaryAccountNumber, txnTotalAmount, txnAfterFeeTaxAmount, txnFee, txnTax, 'cr', NOW(), 'upi_inward_credit', txnNarration, primaryClosingBalance, merchantTxnRefId, serviceId, npciTxnId, originalOrderId, payerVpa, payerAccName, feeRate, NOW(), NOW());
                UPDATE `users` SET `transaction_amount` = (`transaction_amount` + txnAmount) WHERE `id` = userId;
                UPDATE `upi_callbacks` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = upiCallbacksRowId;
                SET flag = 1;
                IF(flag) THEN
                    COMMIT;
                    SET message = 'Primary balance credited successfully';
                ELSE
                    ROLLBACK;
                    SET message = 'Query Error';
                    SET flag = 0;
                END IF;
            END IF;
        ELSE
            SET message = 'UTR not found';
        END IF;
    
    ELSE 
    set message = 'Transaction already credited';
    END IF;

    SELECT JSON_MERGE(JSON_OBJECT('ref_id', inCustomerRefId),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiStackCreditTxnJob`(IN `inUserId` BIGINT(20), IN `inCustomerRefId` VARCHAR(100), IN `serviceId` VARCHAR(100), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnAfterFeeTaxAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `feeRate` VARCHAR(100), IN `inFrequency` VARCHAR(50), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE merchantTxnRefId VARCHAR(200);
DECLARE npciTxnId VARCHAR(200);
DECLARE originalOrderId VARCHAR(200);
DECLARE payerVpa VARCHAR(200);
DECLARE payerAccName VARCHAR(200);
DECLARE upiCallbacksRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;
SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `order_id` = inCustomerRefId limit 1;
    SELECT `id`, `user_id`, `npci_txn_id`, `original_order_id`, `payer_vpa`, `payer_acc_name`, `merchant_txn_ref_id` INTO upiCallbacksRowId, userId, npciTxnId, originalOrderId, payerVpa, payerAccName, merchantTxnRefId FROM `upi_callbacks` WHERE `id` = rowId AND `is_trn_credited` = '0';

    IF(txnPkId is NULL AND upiCallbacksRowId) THEN

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` + txnAmount) WHERE `id` = userId;
        UPDATE `upi_callbacks` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = upiCallbacksRowId;

        SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `service_id`, `udf1`, `udf2`, `udf3`, `udf4`, `fee_rate`, `created_at`, `updated_at`)
            VALUES (txnId, txnReferenceId, userId, inCustomerRefId, primaryAccountNumber, txnTotalAmount, txnAfterFeeTaxAmount, txnFee, txnTax, 'cr', NOW(), 'upi_inward_credit', txnNarration, primaryTxnAmount, merchantTxnRefId, serviceId, npciTxnId, inFrequency, payerVpa, payerAccName, feeRate, NOW(), NOW());
        
        SET flag = 1;
    
    ELSE 
    set message = 'Transaction already credited OR UTR not found';
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Primary balance credited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;

    SELECT JSON_MERGE(JSON_OBJECT('ref_id', inCustomerRefId),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiStackDisputeTxnJob`(IN `inUserId` BIGINT(20), IN `inUpiCallbackId` BIGINT(20), IN `inTxnId` VARCHAR(200), IN `inTxnRefId` VARCHAR(200), IN `inUtr` VARCHAR(100), IN `inTrTotalAmount` DOUBLE(18,2), IN `inTrAmount` DOUBLE(18,2), IN `inTxnFee` DOUBLE(18,2), IN `inTxnTax` DOUBLE(18,2), IN `inTxnNarration` VARCHAR(200), IN `inServiceId` VARCHAR(50), IN `inOldTxnId` VARCHAR(50), IN `inAdminId` VARCHAR(20), OUT `outData` JSON)
BEGIN

DECLARE checkPkId BIGINT(20);
DECLARE upiCallbacksRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE messageOut VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;
SET flag = 0;

    SELECT `id` INTO checkPkId FROM `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = inTxnRefId LIMIT 1;
    SELECT `id` INTO upiCallbacksRowId FROM `upi_callbacks` WHERE `id` = inUpiCallbackId AND `is_trn_credited` = '1' AND `is_trn_disputed` = '0';

    IF(checkPkId IS NULL AND upiCallbacksRowId) THEN

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` - inTrTotalAmount) WHERE `id` = inUserId;
        UPDATE `upi_callbacks` SET `is_trn_disputed` = '1', `trn_disputed_at` = NOW(), `updated_at` = NOW() WHERE `id` = upiCallbacksRowId;

        SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = inUserId;
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `udf1`, `udf2`, `created_at`, `updated_at`)
            VALUES (inTxnId, inTxnRefId, inUserId, inUtr, primaryAccountNumber, CONCAT('-', inTrTotalAmount), inTrAmount, inTxnFee, inTxnTax, 'dr', NOW(), 'upi_inward_dispute', inTxnNarration, primaryTxnAmount, inServiceId, inOldTxnId, inAdminId, NOW(), NOW());
        
        SET flag = 1;
    
    ELSE 
    set messageOut = 'Transaction already debited OR UTR not found';
    END IF;

    IF(flag) THEN
        COMMIT;
        SET messageOut = 'Primary balance debited successfully';
    ELSE
        ROLLBACK;
        SET messageOut = 'Query Error';
        SET flag = 0;
    END IF;
    SELECT JSON_MERGE(JSON_OBJECT('ref_id', inUtr),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', messageOut)) INTO outData;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiStackFeeDebitTransaction`(IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `identifier` VARCHAR(200), IN `txnType` VARCHAR(200), IN `serviceId` VARCHAR(200), IN `feeRate` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE userId BIGINT(20);
DECLARE tableRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE primaryClosingBalance DOUBLE(18,2);
DECLARE drAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

	SELECT `id`, `user_id` INTO tableRowId, userId FROM `upi_merchants` WHERE `id` = rowId AND `is_fee_charged` = '0';
    
    IF(tableRowId) THEN
    	SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
        
        IF(primaryAccountNumber) THEN
        
        	SET drAmount = txnFee + txnTax;
        	SET primaryClosingBalance = primaryTxnAmount - drAmount;
            
            INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
		VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, txnType, NOW(), identifier, txnNarration, primaryClosingBalance, serviceId, feeRate, NOW(), NOW());
        
        	UPDATE `users` SET `transaction_amount` = (`transaction_amount` - drAmount) WHERE `id` = userId;
        
        	UPDATE `upi_merchants` SET `is_fee_charged` = '1', `fee_charged_at` = NOW(), `updated_at` = NOW() WHERE `id` = tableRowId;
            
            SET flag = 1;
        
        	IF(flag) THEN
        		COMMIT;
            	SET message = 'Fee and Tax debited successfully';
        	ELSE
        		ROLLBACK;
            	SET message = 'Query Error';
            	SET flag = 0;
        	END IF;
            
		END IF;
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', txnReferenceId),
                      JSON_OBJECT('message', message),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('status', flag)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiStackFundSettleTxnJob`(IN `inTimestamp` VARCHAR(25), IN `inUserId` BIGINT(20), IN `inBatchId` VARCHAR(55), IN `inRowCounts` INT(10), IN `inServiceId` VARCHAR(55), IN `inTxnId` VARCHAR(55), IN `inTxnRefId` VARCHAR(55), IN `inSignedAfterFeeTaxAmount` VARCHAR(25), IN `inAfterFeeTaxAmount` DOUBLE(18,2), IN `inTrAmount` DOUBLE(18,2), IN `inFee` DOUBLE(18,2), IN `inTax` DOUBLE(18,2), IN `inTxnNarration` TEXT, IN `inFrequency` VARCHAR(55), OUT `outData` JSON)
BEGIN

DECLARE txnId VARCHAR(50) DEFAULT NULL;
DECLARE batchId VARCHAR(55) DEFAULT NULL;
DECLARE accountNumber VARCHAR(100);
DECLARE transactionAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;
SET flag = 0;


    SELECT `batch_id` INTO batchId FROM `upi_callbacks`
        WHERE `user_id` = inUserId AND `batch_id` = inBatchId AND `txn_id` = inTxnId LIMIT 1;

    SELECT `txn_id` INTO txnId FROM `transactions`
        WHERE `user_id` = inUserId AND `txn_ref_id` = inBatchId AND `tr_reference` = inTimestamp LIMIT 1;
    
    
    IF(txnId IS NULL AND batchId IS NOT NULL) THEN

        UPDATE `upi_callbacks` SET `is_trn_credited` = '1', `is_trn_settle` = '1', `trn_credited_at` = NOW(), `trn_settled_at` = NOW(), `updated_at` = NOW() 
            WHERE `user_id` = inUserId AND `batch_id` = inBatchId AND `txn_id` = inTxnId;

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` + inAfterFeeTaxAmount)
            WHERE `id` = inUserId;

        SELECT `account_number`, `transaction_amount` INTO accountNumber, transactionAmount FROM `users`
            WHERE `id` = inUserId;
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `service_id`, `udf1`, `udf2`, `created_at`, `updated_at`)
            VALUES (inTxnId, inTxnRefId, inUserId, inBatchId, accountNumber, inSignedAfterFeeTaxAmount, inTrAmount, inFee, inTax, 'cr', NOW(), 'upi_inward_credit', inTxnNarration, transactionAmount, inTimestamp, inServiceId, inRowCounts, inFrequency, NOW(), NOW());
        
        SET flag = 1;
    
    ELSE 
        SET message = 'UPI Stack balance already settled OR batch ID not found';
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'UPI Stack balance settled successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;

    SELECT JSON_MERGE(JSON_OBJECT('ref_id', inTxnId),
                      JSON_OBJECT('batch_id', inBatchId),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('user_id', inUserId),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiStackVerifyFeeDebitTransaction`(IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `identifier` VARCHAR(200), IN `txnType` VARCHAR(200), IN `serviceId` VARCHAR(200), IN `feeRate` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE userId BIGINT(20);
DECLARE tableRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE primaryClosingBalance DOUBLE(18,2);
DECLARE drAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

	SELECT `id`, `user_id` INTO tableRowId, userId FROM `upi_verify_requests` WHERE `id` = rowId AND `is_fee_charged` = '0';
    
    IF(tableRowId) THEN
    	SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
        
        IF(primaryAccountNumber) THEN
        
        	SET drAmount = txnFee + txnTax;
        	SET primaryClosingBalance = primaryTxnAmount - drAmount;
            
            INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
		VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, txnType, NOW(), identifier, txnNarration, primaryClosingBalance, serviceId, feeRate, NOW(), NOW());
        
        	UPDATE `users` SET `transaction_amount` = (`transaction_amount` - drAmount) WHERE `id` = userId;
        
        	UPDATE `upi_verify_requests` SET `is_fee_charged` = '1', `fee_charged_at` = NOW(), `updated_at` = NOW() WHERE `id` = tableRowId;
            
            SET flag = 1;
        
        	IF(flag) THEN
        		COMMIT;
            	SET message = 'Fee and Tax debited successfully';
        	ELSE
        		ROLLBACK;
            	SET message = 'Query Error';
            	SET flag = 0;
        	END IF;
            
		END IF;
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', txnReferenceId),
                      JSON_OBJECT('message', message),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('status', flag)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiStackVerifyFeeDebitTxnJob`(IN `inUserId` BIGINT(20), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `identifier` VARCHAR(200), IN `txnType` VARCHAR(200), IN `serviceId` VARCHAR(200), IN `feeRate` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE tableRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE drAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = txnReferenceId limit 1;
	SELECT `id`, `user_id` INTO tableRowId, userId FROM `upi_verify_requests` WHERE `id` = rowId AND `is_fee_charged` = '0';
    
    IF(txnPkId is NULL AND tableRowId) THEN

        SET drAmount = txnFee + txnTax;

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` - drAmount) WHERE `id` = userId;
        
        UPDATE `upi_verify_requests` SET `is_fee_charged` = '1', `fee_charged_at` = NOW(), `updated_at` = NOW() WHERE `id` = tableRowId;

    	SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
		    VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, txnType, NOW(), identifier, txnNarration, primaryTxnAmount, serviceId, feeRate, NOW(), NOW());
        
        SET flag = 1;
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Fee and Tax debited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', txnReferenceId),
                      JSON_OBJECT('message', message),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('status', flag)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiStackVpaFeeDebitTxnJob`(IN `inUserId` BIGINT(20), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `identifier` VARCHAR(200), IN `txnType` VARCHAR(200), IN `serviceId` VARCHAR(200), IN `feeRate` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE tableRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE drAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;


    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = txnReferenceId limit 1;
	SELECT `id`, `user_id` INTO tableRowId, userId FROM `upi_merchants` WHERE `id` = rowId AND `is_fee_charged` = '0';
    
    IF(txnPkId is NULL AND tableRowId) THEN

        SET drAmount = txnFee + txnTax;
        
        UPDATE `users` SET `transaction_amount` = (`transaction_amount` - drAmount) WHERE `id` = userId;
        
        UPDATE `upi_merchants` SET `is_fee_charged` = '1', `fee_charged_at` = NOW(), `updated_at` = NOW() WHERE `id` = tableRowId;
        
        SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
		    VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, txnType, NOW(), identifier, txnNarration, primaryTxnAmount, serviceId, feeRate, NOW(), NOW());
        
        SET flag = 1;
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Fee and Tax debited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', txnReferenceId),
                      JSON_OBJECT('message', message),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('status', flag)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `UpiStaticQrCreditTransaction`(IN `inUserId` BIGINT(20), IN `inCustomerRefId` VARCHAR(100), IN `serviceId` VARCHAR(100), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnNarration` TEXT, OUT `outData` JSON)
BEGIN
DECLARE txnPkId BIGINT(20);
DECLARE userId BIGINT(20);
DECLARE merchantTxnRefId VARCHAR(200);
DECLARE npciTxnId VARCHAR(200);
DECLARE originalOrderId VARCHAR(200);
DECLARE payerVpa VARCHAR(200);
DECLARE payerAccName VARCHAR(200);
DECLARE upiCallbacksRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE primaryClosingBalance DOUBLE(18,2);
DECLARE creditedAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;
SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `order_id` = inCustomerRefId limit 1;

    IF(txnPkId is NULL) THEN

	    SELECT `id`, `user_id`, `npci_txn_id`, `original_order_id`, `payer_vpa`, `payer_acc_name`, `merchant_txn_ref_id` INTO upiCallbacksRowId, userId, npciTxnId, originalOrderId, payerVpa, payerAccName, merchantTxnRefId FROM `upi_callbacks` WHERE `id` = rowId AND `is_trn_credited` = '0';

        IF(upiCallbacksRowId) THEN
            SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
            IF(primaryAccountNumber) THEN
                SET creditedAmount = txnAmount;
                SET primaryClosingBalance = primaryTxnAmount + creditedAmount;
                INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `order_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `service_id`, `udf1`, `udf2`, `udf3`, `udf4`, `created_at`, `updated_at`)
            VALUES (txnId, txnReferenceId, userId, inCustomerRefId, primaryAccountNumber, txnTotalAmount, txnAmount, 0, 0, 'cr', NOW(), 'upi_inward_credit', txnNarration, primaryClosingBalance, merchantTxnRefId, serviceId, npciTxnId, originalOrderId, payerVpa, payerAccName, NOW(), NOW());
                UPDATE `users` SET `transaction_amount` = (`transaction_amount` + creditedAmount) WHERE `id` = userId;
                UPDATE `upi_callbacks` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = upiCallbacksRowId;
                SET flag = 1;
                IF(flag) THEN
                    COMMIT;
                    SET message = 'Primary balance credited successfully';
                ELSE
                    ROLLBACK;
                    SET message = 'Query Error';
                    SET flag = 0;
                END IF;
            END IF;
        ELSE
            SET message = 'UTR not found';
        END IF;
    
    ELSE 
    set message = 'Transaction already credited';
    END IF;

    SELECT JSON_MERGE(JSON_OBJECT('ref_id', inCustomerRefId),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `ValidationSuiteFeeChargeTxnJob`(IN `inUserId` BIGINT(20), IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, IN `identifier` VARCHAR(200), IN `txnType` VARCHAR(200), IN `serviceId` VARCHAR(200), IN `feeRate` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE tableRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE drAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_ref_id` = txnReferenceId limit 1;
	SELECT `id` INTO tableRowId FROM `validation_suite` WHERE `id` = rowId AND `is_fee_charged` = '0';
    
    IF(txnPkId IS NULL AND tableRowId IS NOT NULL) THEN

        SET drAmount = txnFee + txnTax;

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` - drAmount) WHERE `id` = inUserId;
        
        UPDATE `validation_suite` SET `is_fee_charged` = '1', `fee_charged_at` = NOW(), `updated_at` = NOW(), `status` = 'processing' WHERE `id` = rowId;

    	SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = inUserId;
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `fee_rate`, `created_at`, `updated_at`)
		    VALUES (txnId, txnReferenceId, inUserId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, txnType, NOW(), identifier, txnNarration, primaryTxnAmount, serviceId, feeRate, NOW(), NOW());
        
        SET flag = 1;
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Fee and Tax debited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', txnReferenceId),
                      JSON_OBJECT('message', message),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('status', flag)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `ValidationSuiteFeeReverseTxnJob`(IN `inUserId` BIGINT(20), IN `inRowId` BIGINT(20), IN `inTxnId` VARCHAR(200), IN `inTxnReferenceId` VARCHAR(200), IN `inTxnTotalAmount` VARCHAR(25), IN `inTxnAmount` DOUBLE(18,2), IN `inTxnFee` DOUBLE(18,2), IN `inTxnTax` DOUBLE(18,2), IN `inTxnNarration` TEXT, IN `inIdentifier` VARCHAR(200), IN `inTxnType` VARCHAR(200), IN `inServiceId` VARCHAR(200), IN `inFeeRate` VARCHAR(100), IN `inOldTxnId` VARCHAR(100), OUT `outData` JSON)
BEGIN

DECLARE txnPkId BIGINT(20);
DECLARE tableRowId BIGINT(20) DEFAULT 0;
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE crAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

    SELECT `id` INTO txnPkId from `transactions` WHERE `user_id` = inUserId AND `txn_id` = inOldTxnId limit 1;
	SELECT `id` INTO tableRowId FROM `validation_suite` WHERE `id` = inRowId AND `is_fee_charged` = '1' AND `is_fee_reversed` = '0';
    
    IF(txnPkId IS NOT NULL AND tableRowId IS NOT NULL) THEN

        SET crAmount = inTxnFee + inTxnTax;

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` + crAmount) WHERE `id` = inUserId;

    	SELECT `account_number`, `transaction_amount` INTO primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = inUserId;

        UPDATE `validation_suite` SET `is_fee_reversed` = '1', `fee_reversed_at` = NOW(), `updated_at` = NOW() WHERE `id` = inRowId;
            
        INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `service_id`, `fee_rate`, `udf1`, `created_at`, `updated_at`)
		    VALUES (inTxnId, inTxnReferenceId, inUserId, primaryAccountNumber, inTxnTotalAmount, inTxnAmount, inTxnFee, inTxnTax, inTxnType, NOW(), inIdentifier, inTxnNarration, primaryTxnAmount, inServiceId, inFeeRate, inOldTxnId, NOW(), NOW());
        
        SET flag = 1;
        
    ELSE
    
    	SET message = 'Data not found';
    
    END IF;

    IF(flag) THEN
        COMMIT;
        SET message = 'Fee and Tax debited successfully';
    ELSE
        ROLLBACK;
        SET message = 'Query Error';
        SET flag = 0;
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', inTxnReferenceId),
                      JSON_OBJECT('message', message),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('status', flag)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `VanFundCreditTransaction`(IN `rowId` BIGINT(20), IN `txnId` VARCHAR(200), IN `txnReferenceId` VARCHAR(200), IN `txnTotalAmount` VARCHAR(25), IN `txnAmount` DOUBLE(18,2), IN `txnFee` DOUBLE(18,2), IN `txnTax` DOUBLE(18,2), IN `txnNarration` TEXT, OUT `outData` JSON)
BEGIN

DECLARE userId BIGINT(20);
DECLARE uniqueTxnNumber VARCHAR(200);
DECLARE txnRefId VARCHAR(200);
DECLARE fundCallbackTableId BIGINT(20) DEFAULT 0;
DECLARE userName VARCHAR(200);
DECLARE userEmail VARCHAR(200);
DECLARE primaryAccountNumber VARCHAR(100);
DECLARE primaryTxnAmount DOUBLE(18,2);
DECLARE primaryClosingBalance DOUBLE(18,2);
DECLARE creditedAmount DOUBLE(18,2);
DECLARE flag INT(1) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

SET flag = 0;

	SELECT `id`, `user_id`, `utr`, `reference_id` INTO fundCallbackTableId, userId, uniqueTxnNumber, txnRefId FROM `fund_receive_callbacks` WHERE `id` = rowId AND `is_trn_credited` = '0';
    
    IF(fundCallbackTableId) THEN
    	SELECT `name`, `email`, `account_number`, `transaction_amount` INTO userName, userEmail, primaryAccountNumber, primaryTxnAmount FROM `users` WHERE `id` = userId;
        
        IF(primaryAccountNumber) THEN
        
        	SET creditedAmount = txnAmount - txnFee - txnTax;
        	SET primaryClosingBalance = primaryTxnAmount + creditedAmount;
            
            
            INSERT INTO `transactions`(`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`, `closing_balance`, `tr_reference`, `udf1`, `created_at`)
		VALUES (txnId, txnReferenceId, userId, primaryAccountNumber, txnTotalAmount, txnAmount, txnFee, txnTax, 'cr', NOW(), 'van_inward_credit', txnNarration, primaryClosingBalance, txnRefId, uniqueTxnNumber, NOW());
        
        	UPDATE `users` SET `transaction_amount` = (`transaction_amount` + creditedAmount) WHERE `id` = userId;
        
        	UPDATE `fund_receive_callbacks` SET `is_trn_credited` = '1', `trn_credited_at` = NOW(), `updated_at` = NOW() WHERE `id` = fundCallbackTableId;
            
            SET flag = 1;
        
        	IF(flag) THEN
        		COMMIT;
            	SET message = 'Primary balance credited successfully';
        	ELSE
        		ROLLBACK;
            	SET message = 'Query Error';
            	SET flag = 0;
        	END IF;
            
		END IF;
        
    ELSE
    
    	SET message = 'UTR not found';
    
    END IF;
    
    SELECT JSON_MERGE(JSON_OBJECT('utr', uniqueTxnNumber),
                      JSON_OBJECT('name', userName),
                      JSON_OBJECT('email', userEmail),
                      JSON_OBJECT('date', NOW()),
                      JSON_OBJECT('account_number', primaryAccountNumber),
                      JSON_OBJECT('status', flag),
                      JSON_OBJECT('message', message)) INTO outData;
    
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `aepsCommissionCreditTransaction`(IN `userId` INT, IN `serviceId` VARCHAR(100), IN `txnIdCr` VARCHAR(100), IN `txnRefIdCr` VARCHAR(100), IN `trIdentifiers` VARCHAR(100), IN `commission` DOUBLE(18,2), IN `finalAmount` DOUBLE(18,2), IN `tds` DOUBLE(18,2), IN `gst` DOUBLE(18,2), IN `idString` LONGTEXT, OUT `data` JSON)
BEGIN
DECLARE trn_pk_id int (11);
DECLARE user_account_number VARCHAR(250);
DECLARE tr_narration_str VARCHAR(250);
DECLARE service_trn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE user_closing_balance DOUBLE(18, 2);
DECLARE user_services_pk_id int (11);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;
	select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = txnRefIdCr limit 1;
	IF(trn_pk_id is NULL) THEN
		
		SELECT transaction_amount, account_number, id INTO service_trn_amount, user_account_number, user_services_pk_id FROM `users`
		WHERE `id` = userId   AND `is_active` = '1';
        IF(user_services_pk_id IS NOT NULL) THEN
			
			UPDATE `aeps_transactions` SET is_commission_credited = '1', commission_credited_at = now(), commission_ref_id = txnRefIdCr WHERE FIND_IN_SET(id, idString);
			UPDATE `users` SET transaction_amount = service_trn_amount + finalAmount WHERE `id` = user_services_pk_id AND `is_active` = '1';
			SET user_closing_balance = service_trn_amount  + finalAmount;
			SET tr_narration_str = CONCAT(finalAmount , ' credited to main wallet against ', txnRefIdCr);
		         
                        INSERT INTO aeps_commissions( user_id, commission_ref_id, c_amount, tds, gst)
				VALUES (userId, txnRefIdCr, commission, tds, gst);
			
			INSERT INTO transactions (user_id, txn_id,txn_ref_id, account_number, tr_total_amount , tr_amount,tr_tds,tr_tax, tr_type, tr_date,tr_identifiers,
                             tr_narration, closing_balance, service_id)
				VALUES (userId, txnIdCr,txnRefIdCr, user_account_number, CONCAT('+', finalAmount), finalAmount,tds,gst, 'cr', now(),trIdentifiers,
				tr_narration_str, user_closing_balance , serviceId );
			SET flag = 1;
            IF (flag) THEN
                COMMIT;
                SET message = 'Amount credit successfully.';
            ELSE
                ROLLBACK;
                SET message = 'DB action failed.';
            END IF;
		ELSE
			SET message = 'No aeps transaction found for credit.';
		END IF;
	ELSE
		SET message = 'Commission allready credited.';
	END IF;
		SELECT JSON_MERGE(JSON_OBJECT('status' , flag), JSON_OBJECT('message' , message)) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `aepsCreditAmountTransaction`(IN `userId` INT, IN `serviceId` VARCHAR(100), IN `txnIdCr` VARCHAR(100), IN `txnRefIdCr` VARCHAR(100), IN `trIdentifiers` VARCHAR(100), IN `fee` VARCHAR(100), IN `tax` VARCHAR(100), IN `amount` DOUBLE(18,2), IN `finalAmount` DOUBLE(18,2), IN `idString` LONGTEXT, OUT `data` JSON)
BEGIN
DECLARE trn_pk_id int (11);
DECLARE user_account_number VARCHAR(250);
DECLARE tr_narration_str VARCHAR(250);
DECLARE service_trn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE user_closing_balance DOUBLE(18, 2);
DECLARE user_services_pk_id int (11);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;

	select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = txnRefIdCr limit 1; 
	IF(trn_pk_id is NULL) THEN
		
		SELECT transaction_amount, account_number, id INTO service_trn_amount, user_account_number, user_services_pk_id FROM `users`
		WHERE `id` = userId   AND `is_active` = '1';

        IF(user_services_pk_id IS NOT NULL) THEN

			
			UPDATE `aeps_transactions` SET is_trn_credited = '1', trn_credited_at = now(), trn_ref_id = txnRefIdCr WHERE FIND_IN_SET(id, idString);
			
			UPDATE `users` SET transaction_amount = service_trn_amount + finalAmount WHERE `id` = user_services_pk_id AND `is_active` = '1';
			SET user_closing_balance = service_trn_amount  + amount;
			SET tr_narration_str = CONCAT(finalAmount, ' credited to Primary Wallet');
		
           
			
			INSERT INTO transactions ( user_id, txn_id,txn_ref_id, account_number, tr_total_amount , tr_amount, tr_type, tr_date,tr_identifiers, tr_narration, closing_balance, service_id, tr_fee, tr_tax)
				VALUES (userId, txnIdCr,txnRefIdCr, user_account_number, CONCAT('+', finalAmount), amount, 'cr', now(),trIdentifiers,
				tr_narration_str, user_closing_balance , serviceId, fee, tax );

			SET flag = 1;
            IF (flag) THEN
                COMMIT;
                SET message = 'Amount credited successfully.';
            ELSE
                ROLLBACK;
                SET message = 'DB action failed.';
            END IF;
		ELSE
			SET message = 'No aeps transaction found .';
		END IF;
	ELSE
		SET message = 'Amount already credited.';
	END IF;
		SELECT JSON_MERGE(JSON_OBJECT('status' , flag), JSON_OBJECT('message' , message)) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `aepsCreditTransaction`(IN `clientRefId` VARCHAR(100), IN `userId` INT, IN `serviceId` VARCHAR(100), IN `txnIdCr` VARCHAR(100), IN `commission` DOUBLE(18,2), IN `tds` DOUBLE(18,2), IN `finalCommission` DOUBLE(18,2), IN `trnType` VARCHAR(50), IN `marginStr` VARCHAR(50), OUT `data` JSON)
BEGIN
DECLARE trn_pk_id int (11);
DECLARE user_account_number VARCHAR(250);
DECLARE tr_narration_str VARCHAR(250);
DECLARE service_trn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE user_closing_balance DOUBLE(18, 2);
DECLARE commissionAmount DOUBLE(18, 2) DEFAULT 0;
DECLARE tr_amounts DOUBLE(18, 2) DEFAULT 0;
DECLARE tdsAmount DOUBLE(18, 2) DEFAULT 0;
DECLARE finalAmount DOUBLE(18, 2);
DECLARE aeps_transactions_amount DOUBLE(18, 2);
DECLARE aeps_transactions_commission_amount DOUBLE(18, 2);
DECLARE aeps_trn_pk_id int (11);
DECLARE user_services_pk_id int (11);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;
	select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = clientRefId limit 1; 
	IF(trn_pk_id is NULL) THEN

		
		SELECT transaction_amount, service_account_number, id INTO service_trn_amount, user_account_number, user_services_pk_id FROM `user_services` WHERE `user_id` = userId AND service_id = serviceId  AND `is_active` = '1';

		SELECT id, transaction_amount INTO aeps_trn_pk_id, aeps_transactions_amount FROM `aeps_transactions` WHERE `user_id` = userId and `client_ref_id` = clientRefId and `is_trn_credited` = '0';
        IF(aeps_trn_pk_id is NOT NULL AND user_services_pk_id IS NOT NULL) THEN
			
				SET commissionAmount = finalCommission;
				SET tdsAmount = tds;
				IF(service_trn_amount IS NULL) THEN
					SET service_trn_amount = 0.00;
				END IF;
				IF(trnType = 'cw' OR trnType = 'ap' ) THEN
					SET tr_amounts = aeps_transactions_amount;
				END IF;
				SET user_closing_balance = service_trn_amount  + aeps_transactions_amount;
				SET finalAmount = commissionAmount + aeps_transactions_amount;
				SET tr_narration_str = CONCAT(finalAmount , ' credited against ', clientRefId);
			
			UPDATE `user_services` SET transaction_amount = service_trn_amount  + aeps_transactions_amount WHERE `id` = user_services_pk_id AND `is_active` = '1';
            
			
			IF(trnType = 'cw' OR trnType = 'ap' ) THEN
				INSERT INTO transactions (txn_id, user_id, txn_ref_id, account_number, tr_total_amount , tr_amount, tr_type, tr_date, tr_commission, tr_tds ,tr_identifiers, tr_narration, closing_balance, remarks, service_id, udf1)
				VALUES (txnIdCr, userId, clientRefId, user_account_number, CONCAT('+', aeps_transactions_amount), tr_amounts, 'cr', now(), 0, 0 ,'aeps_credit_amount', CONCAT(aeps_transactions_amount ,' Amount credited to service account'), user_closing_balance , '', serviceId, trnType );
			
				UPDATE `aeps_transactions` SET is_trn_credited = '1', trn_credited_at = now(), status = 'success', commission = commissionAmount, margin = marginStr, tds = tdsAmount  
				WHERE  `user_id` = userId and `client_ref_id` = clientRefId and `is_trn_credited` = '0';
			ELSE
					UPDATE `aeps_transactions` SET is_trn_credited = '1', trn_credited_at = now(), status = 'success', commission = commissionAmount, margin = marginStr, tds = tdsAmount  
					WHERE  `user_id` = userId and `client_ref_id` = clientRefId and `is_trn_credited` = '0';
			END IF;
			SET flag = 1;
            IF (flag) THEN
                COMMIT;
                SET message = 'Amount credit successfully.';
            ELSE
                ROLLBACK;
                SET message = 'DB action failed.';
            END IF;
		ELSE
			SET message = 'No aeps transaction found for credit.';
		END IF;
	ELSE
		SET message = 'Already amount credited.';
	END IF;
		SELECT JSON_MERGE(JSON_OBJECT('status' , flag), JSON_OBJECT('message' , message)) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `aepsInternalTransfer`(IN `userId` INT, IN `serviceId` VARCHAR(50), IN `payoutServiceId` VARCHAR(50), IN `amount` DOUBLE(18,2), IN `txnIdDr` VARCHAR(100), IN `txnIdCr` VARCHAR(100), IN `remarks` VARCHAR(250), OUT `data` JSON)
BEGIN
DECLARE srv_id VARCHAR(200);
DECLARE pay_user_serivce_account_number VARCHAR(250);
DECLARE user_serivce_account_number VARCHAR(250);
DECLARE pay_ser_trn_amount DOUBLE(18, 2);
DECLARE ser_trn_amount DOUBLE(18, 2);
DECLARE pay_user_closing_balance DOUBLE(18, 2) DEFAULT 0;
DECLARE user_serivce_closing_balance DOUBLE(18, 2)  DEFAULT 0;
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;
	IF amount > 0 THEN
	
		
		SELECT transaction_amount, service_account_number INTO pay_ser_trn_amount, pay_user_serivce_account_number FROM `user_services` WHERE `user_id` = userId AND  service_id = payoutServiceId  FOR UPDATE;
		SELECT transaction_amount, service_account_number INTO ser_trn_amount, user_serivce_account_number FROM `user_services` WHERE `user_id` = userId AND  service_id = serviceId  FOR UPDATE;
        
        SET pay_user_closing_balance = pay_ser_trn_amount + amount;
        SET user_serivce_closing_balance = ser_trn_amount - amount;
        IF (ser_trn_amount >= amount) THEN
			
			UPDATE  user_services  SET   transaction_amount = transaction_amount - amount  WHERE  user_id = userId AND service_id = serviceId;
			UPDATE `user_services` SET transaction_amount = transaction_amount + amount WHERE `user_id` = userId AND service_id = payoutServiceId;
            
            INSERT INTO transactions (txn_id, user_id, account_number, tr_total_amount , tr_amount, tr_type, tr_date, tr_identifiers, tr_narration, closing_balance, remarks, service_id)
			VALUES (txnIdDr, userId, user_serivce_account_number,  CONCAT('-', amount), amount, 'dr', now(), 'aeps_internal_transfer', CONCAT(amount ,' Amount debited from aeps account'), user_serivce_closing_balance, remarks, serviceId);
			
            INSERT INTO transactions (txn_id, user_id, account_number, tr_total_amount , tr_amount, tr_type, tr_date, tr_identifiers, tr_narration, closing_balance, remarks, service_id)
			VALUES (txnIdCr, userId, pay_user_serivce_account_number, CONCAT('+', amount), amount, 'cr', now(), 'aeps_internal_transfer', CONCAT(amount ,' Amount credited to payout service account'), pay_user_closing_balance , remarks, payoutServiceId);
			SET flag = 1;
            IF (flag) THEN
                COMMIT;
                SET message = 'Amount debited successfully';
            ELSE
                ROLLBACK;
                SET message = 'DB action failed';
            END IF;
        ELSE
        	SET message = CONCAT('Insufficient AEPS Balance your balance is : ', ser_trn_amount);
        END IF;
	END IF;
		SELECT JSON_MERGE(JSON_OBJECT('status' , flag), JSON_OBJECT('message' , message)) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `bbpsCommissionCreditTransaction`(IN `userId` INT, IN `serviceId` VARCHAR(100), IN `txnIdCr` VARCHAR(100), IN `txnRefIdCr` VARCHAR(100), IN `trIdentifiers` VARCHAR(100), IN `commission` DOUBLE(18,2), IN `finalAmount` DOUBLE(18,2), IN `tds` DOUBLE(18,2), IN `gst` DOUBLE(18,2), IN `idString` VARCHAR(100), OUT `data` JSON)
BEGIN
DECLARE trn_pk_id int (11);
DECLARE user_account_number VARCHAR(250);
DECLARE tr_narration_str VARCHAR(250);
DECLARE service_trn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE user_closing_balance DOUBLE(18, 2);
DECLARE user_services_pk_id int (11);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;
	select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = txnRefIdCr limit 1;
	IF(trn_pk_id is NULL) THEN
		
		SELECT transaction_amount, account_number, id INTO service_trn_amount, user_account_number, user_services_pk_id FROM `users`
		WHERE `id` = userId   AND `is_active` = '1';
        IF(user_services_pk_id IS NOT NULL) THEN
			
			UPDATE `bbps_transaction` SET is_commission_credited = '1', commission_credited_at = now(), commission_ref_id = txnRefIdCr WHERE id= idString;
			UPDATE `users` SET transaction_amount = service_trn_amount + finalAmount WHERE `id` = user_services_pk_id AND `is_active` = '1';
			SET user_closing_balance = service_trn_amount  + finalAmount;
			SET tr_narration_str = CONCAT(finalAmount , ' credited to main wallet against ', txnRefIdCr);
		         
                        INSERT INTO bbps_commissions( user_id, commission_ref_id, c_amount, tds, gst)
				VALUES (userId, txnRefIdCr, commission, tds, gst);
			
			INSERT INTO transactions (user_id, txn_id,txn_ref_id, account_number, tr_total_amount , tr_amount, tr_type, tr_date,tr_identifiers,
                             tr_narration, closing_balance, service_id)
				VALUES (userId, txnIdCr,txnRefIdCr, user_account_number, CONCAT('+', finalAmount), finalAmount, 'cr', now(),trIdentifiers,
				tr_narration_str, user_closing_balance , serviceId );
			SET flag = 1;
            IF (flag) THEN
                COMMIT;
                SET message = 'Amount credit successfully.';
            ELSE
                ROLLBACK;
                SET message = 'DB action failed.';
            END IF;
		ELSE
			SET message = 'No Recharge transaction found for credit.';
		END IF;
	ELSE
		SET message = 'Commission allready credited.';
	END IF;
		SELECT JSON_MERGE(JSON_OBJECT('status' , flag), JSON_OBJECT('message' , message)) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debitAEPSTwoFAAuth`(IN `userId` INT, IN `txnId` VARCHAR(200), IN `serviceId` VARCHAR(100), IN `trIdentifiers` VARCHAR(200), IN `amount` DOUBLE(18,2), IN `gst` DOUBLE(18,2), OUT `data` JSON)
BEGIN
DECLARE trn_pk_id int (11);
DECLARE user_account_number VARCHAR(250);
DECLARE service_trn_amount DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);
DECLARE ser_tranasaction_amount DOUBLE(18,2);
DECLARE tr_narration_str varchar(200); 
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE ser_old_balance  DOUBLE(18, 2) DEFAULT 0;
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;
select id into trn_pk_id from transactions where user_id = userId AND txn_id = txnId limit 1;
    IF(trn_pk_id is NULL) THEN
        SELECT transaction_amount, account_number INTO service_trn_amount, user_account_number FROM `users`
		WHERE `id` = userId   AND `is_active` = '1';
        set total_amount = ROUND( amount + gst, 2);
        set ser_old_balance = service_trn_amount;
        IF(service_trn_amount > total_amount) THEN
            set tr_narration_str = CONCAT(total_amount , ' AEPS 2FA charged debited');
            UPDATE  users  SET  transaction_amount = transaction_amount - total_amount WHERE  id = userId;
            INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount,  tr_type, tr_fee, tr_tax, tr_date, tr_identifiers, tr_narration,opening_balance, closing_balance, service_id)
					VALUES (txnId, '', userId, user_account_number, CONCAT('-',total_amount), 0, 'dr', amount, gst, now(), '2fa_fund_debit', tr_narration_str,ser_old_balance,
							service_trn_amount - total_amount, serviceId);
            set flag = 1;
             
            IF (flag) THEN
			 
                SELECT `transaction_amount` INTO  ser_tranasaction_amount from users
                                            where id = userId;
                        
                            IF (ser_old_balance = (ser_tranasaction_amount + total_amount)) THEN 
                        
                            COMMIT;
                                    set message = 'Payment balance debited successfully';
                                    set flag = 1;
                                ELSE
                                    ROLLBACK;
                                    set flag = 0;
                                    set message = 'debit_balance_failed';
                            END IF;
                    
            ELSE
                    ROLLBACK;
                    set message = 'Query Error';
            END IF;
      	ELSE
            set message = 'Insufficient funds ';
		
        COMMIT;
        END IF;
ELSE 
   set message = 'Order already processing';
END IF;
SET AUTOCOMMIT = true;
     	SELECT JSON_MERGE(JSON_OBJECT('status', flag),JSON_OBJECT('autocommit', ''), JSON_OBJECT( 'message', message )) INTO DATA;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debitBbpsBalanceOrder`(IN `userId` BIGINT(20), IN `orderRefId` VARCHAR(255), IN `txnId` VARCHAR(255), OUT `data` JSON)
BEGIN 

DECLARE trn_pk_id int (11);
DECLARE flag VARCHAR(200);
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE ord_service_id VARCHAR(200);
DECLARE ser_account_number VARCHAR(250);
DECLARE ser_tranasaction_amount DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);
DECLARE tr_narration_str varchar(200); 
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = false;

START TRANSACTION;
set flag = 0;

select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = orderRefId limit 1; 
IF(trn_pk_id is NULL) THEN

SELECT amount, commission, tax, service_id  INTO ord_amount, ord_fee, ord_tax, ord_service_id FROM bbps_transaction WHERE  
user_id = userId AND order_ref_id = orderRefId  AND status = 'queued' ;
set total_amount = ord_amount;

    SELECT  transaction_amount, account_number INTO ser_tranasaction_amount, ser_account_number  FROM users WHERE is_active = '1' 
        AND id = userId ;
    
    IF(ser_tranasaction_amount > total_amount) THEN
               set tr_narration_str = CONCAT(total_amount , ' debited against ', orderRefId);
            UPDATE  users  SET  transaction_amount = transaction_amount - total_amount WHERE  id = userId;  
 
 UPDATE bbps_transaction  SET  status = 'processing', cron_status = '1' WHERE  user_id = userId AND order_ref_id = orderRefId;
           
 INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount,  tr_type, tr_date, tr_identifiers, tr_narration, closing_balance, service_id)
			VALUES (txnId, orderRefId, userId, ser_account_number, CONCAT('-',total_amount), ord_amount, 'dr', now(), 'bbps_debit', tr_narration_str,
                    ser_tranasaction_amount - total_amount, ord_service_id);
          

          set flag = 1;
             IF (flag) THEN
                   COMMIT;
                   set message = 'Payment balance debited successfully';
                   set flag = 1;
             ELSE
                    ROLLBACK;
                    set message = 'Query Error';
             END IF;
      	ELSE 
        UPDATE bbps_transaction  SET  status = 'failed', failed_message = 'Insufficient funds' WHERE  user_id = userId AND order_ref_id = orderRefId;

       set message = 'Insufficient funds ';
         COMMIT;
        END IF;
   ELSE 
   set message = 'Order already processing';
   END IF;
SET AUTOCOMMIT = true;
     	SELECT JSON_MERGE(JSON_OBJECT('status', flag),JSON_OBJECT('autocommit', ''), JSON_OBJECT( 'message', message )) INTO DATA;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debitDMTFundTransfer`(IN `userId` BIGINT(20), IN `orderRefId` VARCHAR(255), IN `txnId` VARCHAR(255), OUT `data` JSON)
BEGIN 

DECLARE trn_pk_id int (11);
DECLARE flag VARCHAR(200);
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE ord_service_id VARCHAR(200);
DECLARE ser_account_number VARCHAR(250);
DECLARE ser_tranasaction_amount DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);
DECLARE tr_narration_str varchar(200); 
DECLARE message VARCHAR(200);
DECLARE ser_old_balance  DOUBLE(18, 2) DEFAULT 0;
SET AUTOCOMMIT = false;

START TRANSACTION;
set flag = 0;

select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = orderRefId limit 1; 
IF(trn_pk_id is NULL) THEN

SELECT amount, fee, tax, service_id  INTO ord_amount, ord_fee, ord_tax, ord_service_id FROM dmt_fund_transfers WHERE  
user_id = userId AND order_ref_id = orderRefId  AND status = 'queued' ;
set total_amount = ROUND(ord_amount + ord_fee + ord_tax, 2);

    SELECT  service_account_number INTO ser_account_number  FROM user_services WHERE is_active = '1' 
        AND user_id = userId 
        AND service_id = ord_service_id;
	SELECT transaction_amount INTO ser_tranasaction_amount FROM users WHERE id = userId;
    	set ser_old_balance = ser_tranasaction_amount;
    IF(ser_tranasaction_amount > total_amount) THEN
               set tr_narration_str = CONCAT(total_amount , ' debited against ', orderRefId);
            UPDATE  users  SET  transaction_amount = transaction_amount - total_amount WHERE  id = userId;  
 
		 UPDATE dmt_fund_transfers  SET  status = 'processing', cron_status = '1' WHERE  user_id = userId AND order_ref_id = orderRefId;
				   
		 INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount,  tr_type, tr_fee, tr_tax, tr_date, tr_identifiers, tr_narration, closing_balance, service_id)
					VALUES (txnId, orderRefId, userId, ser_account_number, CONCAT('-',total_amount), ord_amount, 'dr', ord_fee, ord_tax, now(), 'dmt_fund_debit', tr_narration_str,
							ser_tranasaction_amount - total_amount, ord_service_id);
          

          set flag = 1;
             IF (flag) THEN
			 
			   SELECT transaction_amount INTO ser_tranasaction_amount FROM users WHERE id = userId;
					  
					  	IF (ser_old_balance = (ser_tranasaction_amount + total_amount)) THEN 
					
						   COMMIT;
								set message = 'Payment balance debited successfully';
								set flag = 1;
							ELSE
								ROLLBACK;
								set flag = 0;
								 set message = 'debit_balance_failed';
						END IF;
                 
             ELSE
                    ROLLBACK;
                    set message = 'Query Error';
             END IF;
      	ELSE 

		IF (ser_tranasaction_amount is  null) THEN
			UPDATE dmt_fund_transfers  SET  status = 'failed', failed_message = 'service down' WHERE  user_id = userId AND order_ref_id = orderRefId;

			set message = 'service down ';
		ELSE
			UPDATE dmt_fund_transfers  SET  status = 'failed', failed_message = 'Insufficient funds' WHERE  user_id = userId AND order_ref_id = orderRefId;

			set message = 'Insufficient funds ';
		END IF;
         COMMIT;
        END IF;
   ELSE 
   set message = 'Order already processing';
   END IF;
SET AUTOCOMMIT = true;
     	SELECT JSON_MERGE(JSON_OBJECT('status', flag),JSON_OBJECT('autocommit', ''), JSON_OBJECT( 'message', message )) INTO DATA;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debitOCRBalanceOrder`(IN `userId` BIGINT(20), IN `amount` DOUBLE(18,2), IN `fee` DOUBLE(18,2), IN `tax` DOUBLE(18,2), IN `orderRefId` VARCHAR(255), IN `txnId` VARCHAR(255), IN `trIdentifiers` VARCHAR(255), OUT `data` JSON)
BEGIN 

DECLARE trn_pk_id int (11);
DECLARE flag VARCHAR(200);
DECLARE ord_service_id VARCHAR(200);
DECLARE ser_account_number VARCHAR(250);
DECLARE ser_tranasaction_amount DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);
DECLARE tr_narration_str varchar(200); 
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = false;

START TRANSACTION;
set flag = 0;

select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = orderRefId limit 1; 
IF(trn_pk_id is NULL) THEN

SELECT service_id  INTO ord_service_id FROM `validations` WHERE  
user_id = userId AND order_ref_id = orderRefId  AND status = 'queued';
set total_amount = ROUND(amount + fee + tax, 2);

    SELECT  transaction_amount, service_account_number INTO ser_tranasaction_amount, ser_account_number  FROM user_services WHERE is_active = '1' 
        AND user_id = userId 
        AND service_id = ord_service_id;
    
    IF(ser_tranasaction_amount > total_amount) THEN
               set tr_narration_str = CONCAT(total_amount , ' debited against ', orderRefId);
			   
            UPDATE  user_services  SET  transaction_amount = transaction_amount - total_amount WHERE  user_id = userId AND service_id = ord_service_id;  
            UPDATE `validations` SET  status = 'pending' WHERE  user_id = userId AND order_ref_id = orderRefId;
            INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount, tr_fee, tr_tax, tr_type, tr_date, tr_identifiers, tr_narration, closing_balance, service_id)
			VALUES (txnId, orderRefId, userId, ser_account_number, CONCAT('-',total_amount), amount, fee, tax, 'dr', now(), trIdentifiers, tr_narration_str,
                    ser_tranasaction_amount - total_amount, ord_service_id);
          

          set flag = 1;
             IF (flag) THEN
                   COMMIT;
                   set message = 'Payment balance debited successfully';
                   set flag = 1;
             ELSE
                    ROLLBACK;
                    set message = 'Query Error';
             END IF;
      	ELSE 
			UPDATE `validations` SET  status = 'failed', failed_message = 'Insufficient funds' WHERE  user_id = userId AND order_ref_id = orderRefId;
			set message = 'Insufficient funds';
			 COMMIT;
        END IF;
   ELSE 
		set message = 'Order already processing';
   END IF;
SET AUTOCOMMIT = true;
     	SELECT JSON_MERGE(JSON_OBJECT('status', flag),JSON_OBJECT('autocommit', ''), JSON_OBJECT( 'message', message )) INTO DATA;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debitPanCardServiceAmount`(IN `userId` BIGINT(20), IN `orderRefId` VARCHAR(255), IN `txnId` VARCHAR(255), OUT `data` JSON)
BEGIN 

DECLARE trn_pk_id int (11);
DECLARE flag VARCHAR(200);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE ord_service_id VARCHAR(200);
DECLARE ser_account_number VARCHAR(250);
DECLARE ser_tranasaction_amount DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);
DECLARE tr_narration_str varchar(200); 
DECLARE message VARCHAR(200);
DECLARE ser_old_balance  DOUBLE(18, 2) DEFAULT 0;
SET AUTOCOMMIT = false;

START TRANSACTION;
set flag = 0;

select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = orderRefId limit 1; 
IF(trn_pk_id is NULL) THEN

SELECT fee, tax, service_id  INTO  ord_fee, ord_tax, ord_service_id FROM pan_txns WHERE  
user_id = userId AND order_ref_id = orderRefId  AND status = 'queued' ;
set total_amount = ROUND( ord_fee + ord_tax, 2);

    SELECT  transaction_amount, service_account_number INTO ser_tranasaction_amount, ser_account_number  FROM user_services WHERE is_active = '1' 
        AND user_id = userId 
        AND service_id = ord_service_id;
    	set ser_old_balance = ser_tranasaction_amount;
    IF(ser_tranasaction_amount > total_amount) THEN
               set tr_narration_str = CONCAT(total_amount , ' debited against ', orderRefId);
            UPDATE  user_services  SET  transaction_amount = transaction_amount - total_amount WHERE  user_id = userId AND service_id = ord_service_id;  
 
		 UPDATE pan_txns  SET  status = 'pending' WHERE  user_id = userId AND order_ref_id = orderRefId;
				   
		 INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount,  tr_type, tr_fee, tr_tax, tr_date, tr_identifiers, tr_narration, closing_balance, service_id)
					VALUES (txnId, orderRefId, userId, ser_account_number, CONCAT('-',total_amount), 0, 'dr', ord_fee, ord_tax, now(), 'pancard_fund_debit', tr_narration_str,
							ser_tranasaction_amount - total_amount, ord_service_id);
          

          set flag = 1;
             IF (flag) THEN
			 
			   SELECT `transaction_amount` INTO  ser_tranasaction_amount from user_services
										where user_id = userId 
										AND service_id = ord_service_id;
					  
					  	IF (ser_old_balance = (ser_tranasaction_amount + total_amount)) THEN 
					
						   COMMIT;
								set message = 'Payment balance debited successfully';
								set flag = 1;
							ELSE
								ROLLBACK;
								set flag = 0;
								 set message = 'debit_balance_failed';
						END IF;
                 
             ELSE
                    ROLLBACK;
                    set message = 'Query Error';
             END IF;
      	ELSE 

		IF (ser_tranasaction_amount is  null) THEN
			UPDATE pan_txns  SET  status = 'failed', failed_message = 'service down' WHERE  user_id = userId AND order_ref_id = orderRefId;

			set message = 'service down ';
		ELSE
			UPDATE pan_txns  SET  status = 'failed', failed_message = 'Insufficient funds' WHERE  user_id = userId AND order_ref_id = orderRefId;

			set message = 'Insufficient funds ';
		END IF;
         COMMIT;
        END IF;
   ELSE 
   set message = 'Order already processing';
   END IF;
SET AUTOCOMMIT = true;
     	SELECT JSON_MERGE(JSON_OBJECT('status', flag),JSON_OBJECT('autocommit', ''), JSON_OBJECT( 'message', message )) INTO DATA;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debitPayoutBalanceOrder`(IN `userId` BIGINT(20), IN `orderRefId` VARCHAR(255), IN `integrationId` VARCHAR(255), IN `txnId` VARCHAR(255), OUT `data` JSON)
BEGIN 

DECLARE trn_pk_id int (11);
DECLARE flag VARCHAR(200);
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE ord_service_id VARCHAR(200);
DECLARE ser_account_number VARCHAR(250);
DECLARE ser_transaction_amount DOUBLE(18, 2);
DECLARE ser_old_balance DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);
DECLARE tr_narration_str varchar(200); 
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = false;
 
START TRANSACTION;
set flag = 0;

select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = orderRefId limit 1; 

IF(trn_pk_id is NULL) THEN

SELECT amount, fee, tax, service_id  INTO ord_amount, ord_fee, ord_tax, ord_service_id FROM orders WHERE  
user_id = userId AND order_ref_id = orderRefId  AND status = 'queued' AND cron_status = '0' ;
set total_amount = ROUND(ord_amount + ord_fee + ord_tax, 2);

    SELECT  transaction_amount, service_account_number INTO ser_transaction_amount, ser_account_number  FROM user_services WHERE is_active = '1' 
        AND user_id = userId 
        AND service_id = ord_service_id;
    
	set ser_old_balance = ser_transaction_amount;
	
    IF(ser_transaction_amount > total_amount) THEN
            set tr_narration_str = CONCAT(total_amount , ' debited against ', orderRefId);
            
            UPDATE  user_services  SET  transaction_amount = transaction_amount - total_amount  WHERE  user_id = userId AND service_id = ord_service_id;  
            
            UPDATE orders  SET  status = 'processing', cron_status = '1', integration_id = integrationId WHERE  user_id = userId AND order_ref_id = orderRefId;
            
            INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount, tr_fee, tr_tax, tr_type, tr_date, tr_identifiers, tr_narration,opening_balance, closing_balance, service_id)
            
            VALUES (txnId, orderRefId, userId, ser_account_number, CONCAT('-',total_amount), ord_amount, ord_fee, ord_tax, 'dr', now(), 'payout_disbursement', tr_narration_str,ser_transaction_amount,
                    ser_transaction_amount - total_amount, ord_service_id);
            

                set flag = 1;
                IF (flag) THEN
                SELECT  transaction_amount INTO ser_transaction_amount  FROM user_services WHERE is_active = '1' 
                    AND user_id = userId 
                    AND service_id = ord_service_id;
                
                IF (ser_old_balance = (ser_transaction_amount + total_amount)) THEN 
                    
                    COMMIT;
                    set message = 'Payment balance debited successfully';
                    set flag = 1;
                ELSE
                    ROLLBACK;
                    set flag = 0;
                    set message = 'debit_balance_failed';
                END IF;
                    
                ELSE
                    ROLLBACK;
                        set flag = 0;
                    set message = 'Query Error';
                END IF;
        ELSE 
        UPDATE orders  SET  status = 'failed', failed_message = 'Insufficient funds' WHERE  user_id = userId AND order_ref_id = orderRefId;
        set message = 'Insufficient funds';
        END IF;
    ELSE 
    set message = 'Order already processing';
    END IF;
    SET AUTOCOMMIT = true;
        SELECT JSON_MERGE(JSON_OBJECT('status', flag),JSON_OBJECT('autocommit', ''), JSON_OBJECT( 'message', message )) INTO DATA;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debitRechargeBalanceOrder`(IN `userId` BIGINT(20), IN `orderRefId` VARCHAR(255), IN `txnId` VARCHAR(255), OUT `data` JSON)
BEGIN 

DECLARE trn_pk_id int (11);
DECLARE flag VARCHAR(200);
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE ord_service_id VARCHAR(200);
DECLARE ser_account_number VARCHAR(250);
DECLARE ser_tranasaction_amount DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);
DECLARE tr_narration_str varchar(200); 
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = false;

START TRANSACTION;
set flag = 0;

select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = orderRefId limit 1; 
IF(trn_pk_id is NULL) THEN

SELECT amount, commission, tax, service_id  INTO ord_amount, ord_fee, ord_tax, ord_service_id FROM recharges WHERE  
user_id = userId AND order_ref_id = orderRefId  AND status = 'queued' ;
set total_amount = ord_amount;

    SELECT  transaction_amount INTO ser_tranasaction_amount  FROM users WHERE is_active = '1' 
        AND id = userId;
    
    IF(ser_tranasaction_amount > total_amount) THEN
               set tr_narration_str = CONCAT(total_amount , ' debited against ', orderRefId);
            UPDATE  users  SET  transaction_amount = transaction_amount - total_amount WHERE  id = userId;  
 
 UPDATE recharges  SET  status = 'processing', cron_status = '1' WHERE  user_id = userId AND order_ref_id = orderRefId;
           
 INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount,  tr_type, tr_date, tr_identifiers, tr_narration, closing_balance, service_id)
			VALUES (txnId, orderRefId, userId, ser_account_number, CONCAT('-',total_amount), ord_amount, 'dr', now(), 'recharge_debit', tr_narration_str,
                    ser_tranasaction_amount - total_amount, ord_service_id);
          

          set flag = 1;
             IF (flag) THEN
                   COMMIT;
                   set message = 'Payment balance debited successfully';
                   set flag = 1;
             ELSE
                    ROLLBACK;
                    set message = 'Query Error';
             END IF;
      	ELSE 
        UPDATE recharges  SET  status = 'failed', failed_message = 'Insufficient funds' WHERE  user_id = userId AND order_ref_id = orderRefId;

       set message = 'Insufficient funds ';
         COMMIT;
        END IF;
   ELSE 
   set message = 'Order already processing';
   END IF;
SET AUTOCOMMIT = true;
     	SELECT JSON_MERGE(JSON_OBJECT('status', flag),JSON_OBJECT('autocommit', ''), JSON_OBJECT( 'message', message )) INTO DATA;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debitSettlementBalanceOrder`(IN `userId` BIGINT(20), IN `orderRefId` VARCHAR(255), IN `orderId` VARCHAR(255), IN `integrationId` VARCHAR(255), IN `serviceId` VARCHAR(255), IN `txnId` VARCHAR(255), IN `thresholdAmount` DOUBLE(18,2), OUT `data` JSON)
BEGIN 

DECLARE trn_pk_id int (11);
DECLARE flag VARCHAR(200);
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE ser_account_number VARCHAR(250);
DECLARE ser_tranasaction_amount DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);

DECLARE tr_narration_str varchar(200); 
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = false;

START TRANSACTION;
set flag = 0;

select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = orderRefId limit 1; 
IF(trn_pk_id is NULL) THEN

SELECT amount, fee, tax INTO ord_amount, ord_fee, ord_tax FROM user_settlements WHERE  
user_id = userId AND settlement_ref_id = orderRefId  AND status = 'initiate' ;
set total_amount = ROUND(ord_amount + ord_fee + ord_tax, 2);

    SELECT  transaction_amount, account_number INTO ser_tranasaction_amount, ser_account_number  FROM users WHERE is_active = '1' 
        AND id= userId;
    
    IF(ser_tranasaction_amount >= total_amount) THEN
            UPDATE  users  SET  transaction_amount = transaction_amount - total_amount  WHERE  id = userId;
			UPDATE user_settlements  SET  status = 'processing', is_balance_debited = '1' WHERE  user_id = userId AND settlement_ref_id = orderRefId;
			UPDATE user_settlement_logs  SET  status = 'processing', integration_id = integrationId WHERE  user_id = userId AND settlement_ref_id = orderRefId;
			set tr_narration_str = CONCAT(total_amount , ' debited against ', orderRefId);
            INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount, tr_fee, tr_tax, tr_type, tr_date, tr_identifiers, tr_narration, closing_balance, service_id)
			VALUES (txnId, orderRefId, userId, ser_account_number, CONCAT('-',total_amount), ord_amount, ord_fee, ord_tax, 'dr', now(), 'stlmnt_disbursement', tr_narration_str,
                    ser_tranasaction_amount - total_amount, serviceId);
          
             set flag = 1;
             IF (flag) THEN
                   COMMIT;
                   set message = 'Payment balance debited successfully';
                   set flag = 1;
             ELSE
                    ROLLBACK;
                    set message = 'Query Error';
             END IF;
      	ELSE 
			UPDATE user_settlements  SET  status = 'failed' WHERE  user_id= userId AND settlement_ref_id = orderRefId;
			UPDATE user_settlement_logs  SET  status = 'failed', failed_message= 'Insufficient funds'  WHERE  user_id = userId AND settlement_ref_id = orderRefId;

      	set message = 'Insufficient funds';
        END IF;
   ELSE 
   set message = 'Order already processing';
   END IF;
     	SELECT JSON_MERGE(JSON_OBJECT('status', flag),JSON_OBJECT('autocommit', ''), JSON_OBJECT( 'message', message )) INTO DATA;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `debitValidationBalanceOrder`(IN `userId` BIGINT(20), IN `amount` DOUBLE(18,2), IN `fee` DOUBLE(18,2), IN `tax` DOUBLE(18,2), IN `orderRefId` VARCHAR(255), IN `txnId` VARCHAR(255), IN `trIdentifiers` VARCHAR(255), OUT `outData` JSON)
BEGIN 

DECLARE trn_pk_id int (11);
DECLARE flag VARCHAR(200);
DECLARE ord_service_id VARCHAR(200);
DECLARE ser_account_number VARCHAR(250);
DECLARE ser_tranasaction_amount DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);
DECLARE tr_narration_str varchar(200);
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = false;

START TRANSACTION;

set flag = 0;

SELECT `id` into trn_pk_id FROM `transactions` 
    WHERE `user_id` = userId AND `txn_ref_id` = orderRefId LIMIT 1;

IF(trn_pk_id is NULL) THEN

    SELECT `service_id` INTO ord_service_id FROM `validations` 
        WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = 'queued';

    set total_amount = ROUND(amount + fee + tax, 2);

    SELECT  `service_account_number` INTO  ser_account_number FROM `user_services` 
        WHERE `is_active` = '1' AND `user_id` = userId AND `service_id` = ord_service_id;
	SELECT `transaction_amount` INTO ser_tranasaction_amount FROM `users` 
        WHERE `is_active` = '1' AND `id` = userId;

    IF(ser_tranasaction_amount > total_amount) THEN

        set tr_narration_str = CONCAT(total_amount, ' debited against ', orderRefId);

        UPDATE `users` SET `transaction_amount` = (`transaction_amount` - total_amount) 
            WHERE `id` = userId;

        UPDATE `validations` SET `status` = 'pending'
            WHERE `user_id` = userId AND `order_ref_id` = orderRefId;

        INSERT INTO `transactions` (`txn_id`, `txn_ref_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_fee`, `tr_tax`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`,`opening_balance`, `closing_balance`, `service_id`) 
            VALUES ( txnId, orderRefId, userId, ser_account_number, CONCAT('-', total_amount), amount, fee, tax, 'dr', now(), trIdentifiers, tr_narration_str, ser_tranasaction_amount,(ser_tranasaction_amount - total_amount), ord_service_id);

        set flag = 1;

        IF (flag) THEN COMMIT;

            set message = 'Payment balance debited successfully';
            set flag = 1;

        ELSE ROLLBACK;

            set message = 'Query Error';

        END IF;

    ELSE

        UPDATE `validations` SET `status` = 'failed', `failed_message` = 'Insufficient funds' 
            WHERE `user_id` = userId AND `order_ref_id` = orderRefId;

        set message = 'Insufficient funds';

        COMMIT;

    END IF;

ELSE

set message = 'Order already processing';

END IF;

SET AUTOCOMMIT = true;

SELECT JSON_MERGE(
        JSON_OBJECT('status', flag),
        JSON_OBJECT('autocommit', ''),
        JSON_OBJECT('message', message)
    ) INTO outData;

END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `dmtCashback`(IN `userId` BIGINT(20), IN `orderRefId` VARCHAR(255), IN `txnId` VARCHAR(255), IN `cashback` VARCHAR(255), IN `margin` VARCHAR(255), OUT `data` JSON)
BEGIN 

DECLARE trn_pk_id int (11);
DECLARE flag VARCHAR(200);
DECLARE ord_service_id VARCHAR(200);
DECLARE ser_account_number VARCHAR(250);
DECLARE ser_tranasaction_amount DOUBLE(18, 2);
DECLARE total_amount DOUBLE(18, 2);
DECLARE tr_narration_str varchar(200); 
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = false;

START TRANSACTION;
set flag = 0;

select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = orderRefId  AND tr_type = 'cr' limit 1; 
IF(trn_pk_id is NULL) THEN

SELECT service_id  INTO ord_service_id FROM dmt_fund_transfers WHERE  
user_id = userId AND order_ref_id = orderRefId  AND status = 'processed' AND is_cashback_credited = '0' ;
set total_amount = cashback;

    SELECT  transaction_amount, account_number INTO ser_tranasaction_amount, ser_account_number  FROM users WHERE is_active = '1' 
        AND id = userId;
    
    IF(total_amount > 0 AND ord_service_id is not null) THEN
               set tr_narration_str = CONCAT(total_amount , ' cashback credited against ', orderRefId);
            UPDATE  users  SET  transaction_amount = transaction_amount + total_amount WHERE  id = userId;  
 
 UPDATE dmt_fund_transfers  SET  is_cashback_credited = '1', cashback_credited_at = now(), cashback = total_amount, txt_1 = margin  WHERE  user_id = userId AND order_ref_id = orderRefId;
           
 INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount,  tr_type, tr_date, tr_identifiers, tr_narration, closing_balance, service_id)
			VALUES (txnId, orderRefId, userId, ser_account_number, CONCAT('+',total_amount), total_amount, 'cr', now(), 'dmt_cashback_credit', tr_narration_str,
                    ser_tranasaction_amount + total_amount, ord_service_id);
          

          set flag = 1;
             IF (flag) THEN
                   COMMIT;
                   set message = 'Cashback amount credited successfully';
                   set flag = 1;
             ELSE
                    ROLLBACK;
                    set message = 'Query Error';
             END IF;
      	ELSE 

       set message = 'Invalid amount ';
         COMMIT;
        END IF;
   ELSE 
   set message = 'Cashback already credited';
   END IF;
SET AUTOCOMMIT = true;
     	SELECT JSON_MERGE(JSON_OBJECT('status', flag),JSON_OBJECT('autocommit', ''), JSON_OBJECT( 'message', message )) INTO DATA;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `dmtStatusUpdate`(IN `orderRefId` VARCHAR(250), IN `userId` INT, IN `serviceId` INT, IN `ordStatus` VARCHAR(50), IN `txnId` VARCHAR(50), IN `errorDescription` VARCHAR(255), IN `statusCode` VARCHAR(50), IN `utr` VARCHAR(200), OUT `data` JSON)
BEGIN
DECLARE srv_id INT DEFAULT 0;
DECLARE srv_acc_number VARCHAR(150) DEFAULT NULL;
DECLARE ord_service_id VARCHAR(250) ;
DECLARE srv_txn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE pk_id INT DEFAULT 0;
DECLARE totalAmount  DOUBLE(18, 2) DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE checkStatus VARCHAR(200);
DECLARE ord_cashback DOUBLE(18, 2) DEFAULT 0;
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE ord_is_cashback_credited VARCHAR(25) DEFAULT 0;
DECLARE ser_tranasaction_amount VARCHAR(225) DEFAULT 0;
DECLARE ser_account_number VARCHAR(225) DEFAULT 0;
DECLARE ser_locked_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE tr_narration_str varchar(200); 
	SET AUTOCOMMIT = 0;

	START TRANSACTION;
    set flag = 0;

	IF (ordStatus = 'failed' OR ordStatus = 'reversed') THEN
	

	
	   IF (ordStatus = 'failed') THEN
			SET checkStatus = 'processing';
		ELSE
			SET checkStatus = 'processed';
		END IF;
	
	SELECT `amount`, `id`, `service_id`, `fee`, `tax`, `is_cashback_credited`, `cashback` INTO ord_amount, pk_id, ord_service_id, ord_fee, ord_tax, ord_is_cashback_credited, ord_cashback
			FROM `dmt_fund_transfers`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = checkStatus AND `cron_status` = '1';
			
	set totalAmount = ROUND(ord_amount + ord_fee + ord_tax, 2);
        IF (pk_id != '') THEN
 
            SELECT `id` INTO srv_id
                FROM `user_services`
                WHERE `id` = serviceId;
				
               
		if(srv_id != '') THEN
                    set tr_narration_str = CONCAT(totalAmount , ' credited against ', orderRefId);
                    
		 SELECT `id` INTO srv_id
                FROM `user_services`
                WHERE `id` = serviceId;
		SELECT `transaction_amount`,`account_number` INTO srv_txn_amount,srv_acc_number FROM `users` WHERE id = userId;
				
					INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_fee`, `tr_tax`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`, `closing_balance`, `service_id`, `created_at`)
                      VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, ord_amount, CONCAT('+',totalAmount), ord_fee, ord_tax , NOW(), 'cr', 'dmt_fund_refund', tr_narration_str  , utr, srv_txn_amount + totalAmount, ord_service_id, NOW());
			
			UPDATE users SET transaction_amount  = transaction_amount + totalAmount    WHERE  `id` = userId;
		
			UPDATE `dmt_fund_transfers` SET `status` = ordStatus,   failed_message = errorDescription, updated_at = NOW() WHERE  `order_ref_id` = orderRefId AND `id` = pk_id;
			
			if(srv_id != '' AND ord_is_cashback_credited = '1') THEN
			   SELECT  transaction_amount, account_number INTO ser_tranasaction_amount, ser_account_number  FROM users WHERE is_active = '1' AND id = userId;
		
			    set tr_narration_str = CONCAT(ord_cashback , ' cashback debited against ', orderRefId);
				
				UPDATE  users  SET  transaction_amount = transaction_amount - ord_cashback WHERE  id = userId;  
 
		        UPDATE dmt_fund_transfers  SET   txt_2 = 'reversed'  WHERE  user_id = userId AND order_ref_id = orderRefId;
					   
			    INSERT INTO transactions (txn_id, txn_ref_id, user_id, account_number, tr_total_amount , tr_amount,  tr_type, tr_date, tr_identifiers, tr_narration, closing_balance, service_id)
						VALUES (txnId, orderRefId, userId, ser_account_number, CONCAT('-',ord_cashback), ord_cashback, 'dr', now(), 'dmt_cashback_debited', tr_narration_str, ser_tranasaction_amount - ord_cashback, ord_service_id);
			end if;
					
					
			set flag = 1;
                      IF (flag) THEN
                           COMMIT;
                          set message = 'Payment balance Credited successfully';
                     ELSE
                    ROLLBACK;
                        set message = 'Query Error';
                    END IF;
                 ELSE
              
                 set message = CONCAT('User Service account not found ' );

                END IF;


        ELSE
		
        set message = 'Allready amount refunded';
		
        END IF;
	END IF;
	
SET AUTOCOMMIT = true;
	SELECT JSON_MERGE(JSON_OBJECT('status', flag), JSON_OBJECT( 'message', message )) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `internalTransfer`(IN `userId` INT, IN `serviceId` VARCHAR(50), IN `amount` DOUBLE(18,2), IN `txnIdDr` VARCHAR(100), IN `txnIdCr` VARCHAR(100), IN `remarks` VARCHAR(250), OUT `data` JSON)
BEGIN
DECLARE srv_id VARCHAR(200);
DECLARE user_account_number VARCHAR(250);
DECLARE user_serivce_account_number VARCHAR(250);
DECLARE main_trn_amount DOUBLE(18, 2);
DECLARE ser_trn_amount DOUBLE(18, 2);
DECLARE user_closing_balance DOUBLE(18, 2) DEFAULT 0;
DECLARE user_serivce_closing_balance DOUBLE(18, 2)  DEFAULT 0;
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;
	IF amount > 0 THEN
		
		SELECT transaction_amount, account_number INTO main_trn_amount, user_account_number FROM `users` WHERE `id` = userId AND `is_active` = '1';
		SELECT transaction_amount, service_account_number INTO ser_trn_amount, user_serivce_account_number FROM `user_services` WHERE `user_id` = userId AND  service_id = serviceId;
        
        
        SET user_closing_balance = main_trn_amount - amount;
        SET user_serivce_closing_balance = ser_trn_amount + amount;
        IF (main_trn_amount >= amount) THEN

			
			UPDATE `users` SET transaction_amount = transaction_amount - amount WHERE `id` = userId AND `is_active` = '1';
            UPDATE  user_services  SET   transaction_amount = transaction_amount + amount  WHERE  user_id = userId AND service_id = serviceId;
            
            
            INSERT INTO transactions (`txn_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`,`opening_balance`, `closing_balance`, `remarks`)
			VALUES (txnIdDr, userId, user_account_number,  CONCAT('-', amount), amount, 'dr', now(), 'internal_transfer', CONCAT(amount ,' Amount debited from main account'),main_trn_amount, user_closing_balance, remarks);

			
            INSERT INTO transactions (`txn_id`, `user_id`, `account_number`, `tr_total_amount`, `tr_amount`, `tr_type`, `tr_date`, `tr_identifiers`, `tr_narration`,`opening_balance`, `closing_balance`, `remarks`, `service_id`)
			VALUES (txnIdCr, userId, user_serivce_account_number, CONCAT('+', amount), amount, 'cr', now(), 'internal_transfer', CONCAT(amount ,' Amount credited to service account'),ser_trn_amount, user_serivce_closing_balance , remarks, serviceId);
			SET flag = 1;
            IF (flag) THEN
                COMMIT;
                SET message = 'Amount debited successfully';
            ELSE
                ROLLBACK;
                SET message = 'DB action failed';
            END IF;
        ELSE
        	SET message = CONCAT('Insufficient Main Balance your balance is : ', main_trn_amount);
        END IF;
	END IF;
		SELECT JSON_MERGE(JSON_OBJECT('status' , flag), JSON_OBJECT('message' , message)) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `matmCommissionCreditTransaction`(IN `userId` INT, IN `serviceId` VARCHAR(100), IN `txnIdCr` VARCHAR(100), IN `txnRefIdCr` VARCHAR(100), IN `trIdentifiers` VARCHAR(100), IN `commission` DOUBLE(18,2), IN `finalAmount` DOUBLE(18,2), IN `tds` DOUBLE(18,2), IN `gst` DOUBLE(18,2), IN `idString` LONGTEXT, OUT `data` JSON)
BEGIN
DECLARE trn_pk_id int (11);
DECLARE user_account_number VARCHAR(250);
DECLARE tr_narration_str VARCHAR(250);
DECLARE service_trn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE user_closing_balance DOUBLE(18, 2);
DECLARE user_services_pk_id int (11);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;

	select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = txnRefIdCr limit 1; 
	IF(trn_pk_id is NULL) THEN
		
		SELECT transaction_amount, account_number, id INTO service_trn_amount, user_account_number, user_services_pk_id FROM `users`
		WHERE `id` = userId   AND `is_active` = '1';

        IF(user_services_pk_id IS NOT NULL) THEN

			
			UPDATE `matm_transactions` SET is_commission_credited = '1', commission_credited_at = now(), commission_ref_id = txnRefIdCr WHERE FIND_IN_SET(id, idString);
			UPDATE `users` SET transaction_amount = service_trn_amount + finalAmount WHERE `id` = user_services_pk_id AND `is_active` = '1';
			SET user_closing_balance = service_trn_amount  + finalAmount;
			SET tr_narration_str = CONCAT(finalAmount , ' credited to main wallet against ', txnRefIdCr);

		         
                        INSERT INTO aeps_commissions( user_id, commission_ref_id, c_amount, tds, gst, type)
				VALUES (userId, txnRefIdCr, commission, tds, gst, 'matm');
			
			INSERT INTO transactions (user_id, txn_id,txn_ref_id, account_number, tr_total_amount , tr_amount, tr_type, tr_date,tr_identifiers, 
                             tr_narration, closing_balance, service_id)
				VALUES (userId, txnIdCr,txnRefIdCr, user_account_number, CONCAT('+', finalAmount), finalAmount, 'cr', now(),trIdentifiers,
				tr_narration_str, user_closing_balance , serviceId );

			SET flag = 1;
            IF (flag) THEN
                COMMIT;
                SET message = 'Amount credit successfully.';
            ELSE
                ROLLBACK;
                SET message = 'DB action failed.';
            END IF;
		ELSE
			SET message = 'No aeps transaction found for credit.';
		END IF;
	ELSE
		SET message = 'Commission allready credited.';
	END IF;
		SELECT JSON_MERGE(JSON_OBJECT('status' , flag), JSON_OBJECT('message' , message)) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `matmCreditAmountTransaction`(IN `userId` INT, IN `serviceId` VARCHAR(100), IN `txnIdCr` VARCHAR(100), IN `txnRefIdCr` VARCHAR(100), IN `trIdentifiers` VARCHAR(100), IN `fee` VARCHAR(100), IN `tax` VARCHAR(100), IN `amount` DOUBLE(18,2), IN `finalAmount` DOUBLE(18,2), IN `idString` LONGTEXT, OUT `data` JSON)
BEGIN
DECLARE trn_pk_id int (11);
DECLARE user_account_number VARCHAR(250);
DECLARE tr_narration_str VARCHAR(250);
DECLARE service_trn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE user_closing_balance DOUBLE(18, 2);
DECLARE user_services_pk_id int (11);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);

SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;

	select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = txnRefIdCr limit 1; 
	IF(trn_pk_id is NULL) THEN
		
		SELECT transaction_amount, account_number, id INTO service_trn_amount, user_account_number, user_services_pk_id FROM `users`
		WHERE `id` = userId   AND `is_active` = '1';

        IF(user_services_pk_id IS NOT NULL) THEN

			
			UPDATE `matm_transactions` SET is_trn_credited = '1', trn_credited_at = now(), trn_ref_id = txnRefIdCr WHERE FIND_IN_SET(id, idString);
			
			UPDATE `users` SET transaction_amount = service_trn_amount + finalAmount WHERE `id` = user_services_pk_id AND `is_active` = '1';
			SET user_closing_balance = service_trn_amount  + amount;
			SET tr_narration_str = CONCAT(finalAmount, ' credited to Primary Wallet');
		
           
			
			INSERT INTO transactions ( user_id, txn_id,txn_ref_id, account_number, tr_total_amount , tr_amount, tr_type, tr_date,tr_identifiers, tr_narration, closing_balance, service_id, tr_fee, tr_tax)
				VALUES (userId, txnIdCr,txnRefIdCr, user_account_number, CONCAT('+', finalAmount), amount, 'cr', now(),trIdentifiers,
				tr_narration_str, user_closing_balance , serviceId, fee, tax );

			SET flag = 1;
            IF (flag) THEN
                COMMIT;
                SET message = 'Amount credited successfully.';
            ELSE
                ROLLBACK;
                SET message = 'DB action failed.';
            END IF;
		ELSE
			SET message = 'No aeps transaction found .';
		END IF;
	ELSE
		SET message = 'Amount already credited.';
	END IF;
		SELECT JSON_MERGE(JSON_OBJECT('status' , flag), JSON_OBJECT('message' , message)) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `ocrStatusUpdate`(IN `orderRefId` VARCHAR(250), IN `userId` INT, IN `serviceId` INT, IN `ordStatus` VARCHAR(50), IN `txnId` VARCHAR(50), IN `errorDescription` VARCHAR(255), IN `statusCode` VARCHAR(50), IN `utr` VARCHAR(200), IN `trIdentifiers` VARCHAR(200), OUT `data` JSON)
BEGIN
DECLARE srv_id INT DEFAULT 0;
DECLARE srv_acc_number VARCHAR(150) DEFAULT NULL;
DECLARE ord_service_id VARCHAR(250) ;
DECLARE srv_txn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE pk_id INT DEFAULT 0;
DECLARE totalAmount  DOUBLE(18, 2) DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE flag VARCHAR(25) DEFAULT 0;

DECLARE tr_narration_str varchar(200); 
	SET AUTOCOMMIT = 0;

	START TRANSACTION;
    set flag = 0;
	IF ordStatus = 'failed' THEN
	 	SELECT  `id`, `service_id` INTO pk_id, ord_service_id
			FROM `validations`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND status = 'pending';
        IF (pk_id != '') THEN
		
			SELECT `tr_amount`, `tr_fee`, `tr_tax` INTO ord_amount, ord_fee, ord_tax
					FROM `transactions`
					WHERE `user_id` = userId AND `txn_ref_id` = orderRefId limit 1;
			
			set totalAmount = ROUND(ord_amount + ord_fee + ord_tax, 2);	
	
            SELECT `id` INTO srv_id
                FROM `user_services`
                WHERE `id` = serviceId AND is_active = '1';
				
               
		if(srv_id != '') THEN
                    set tr_narration_str = CONCAT(totalAmount , ' credited against ', orderRefId);
                    
		 SELECT  `service_account_number`, `transaction_amount` INTO srv_acc_number, srv_txn_amount
                FROM `user_services`
                WHERE `id` = serviceId;
					INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_fee`, `tr_tax`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`, `closing_balance`, `service_id`, `created_at`)
                      VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, ord_amount, CONCAT('+',totalAmount) , ord_fee, ord_tax, NOW(), 'cr', trIdentifiers, tr_narration_str  , utr,(SELECT  `transaction_amount` 
                FROM `user_services`
                WHERE `id` = serviceId ) + totalAmount, ord_service_id, NOW());
			
			UPDATE user_services SET transaction_amount   = transaction_amount + totalAmount    WHERE  `id` = serviceId;
		
			UPDATE `validations` SET `status` = ordStatus, failed_message = errorDescription, updated_at = NOW() WHERE  `id` = pk_id;
			set flag = 1;
                      IF (flag) THEN
                           COMMIT;
                          set message = 'Payment balance Credited successfully';
                     ELSE
                    ROLLBACK;
                        set message = 'Query Error';
                    END IF;
                 ELSE
              
                 set message = CONCAT('User Service account not found ' );

                END IF;


        ELSE
		
        set message = 'Allready amount refunded';
		
        END IF;
	END IF;
SET AUTOCOMMIT = true;
	SELECT JSON_MERGE(JSON_OBJECT('status', flag), JSON_OBJECT( 'message', message )) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `panStatusUpdate`(IN `orderRefId` VARCHAR(250), IN `userId` INT, IN `serviceId` INT, IN `ordStatus` VARCHAR(50), IN `txnId` VARCHAR(50), IN `errorDescription` VARCHAR(255), IN `statusCode` VARCHAR(50), IN `utr` VARCHAR(200), OUT `data` JSON)
BEGIN
DECLARE srv_id INT DEFAULT 0;
DECLARE srv_acc_number VARCHAR(150) DEFAULT NULL;
DECLARE ord_service_id VARCHAR(250) ;
DECLARE srv_txn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE pk_id INT DEFAULT 0;
DECLARE totalAmount  DOUBLE(18, 2) DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE checkStatus VARCHAR(200);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE ser_tranasaction_amount VARCHAR(225) DEFAULT 0;
DECLARE ser_account_number VARCHAR(225) DEFAULT 0;
DECLARE ser_locked_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE tr_narration_str varchar(200); 
	SET AUTOCOMMIT = 0;

	START TRANSACTION;
    set flag = 0;

	IF (ordStatus = 'failed') THEN
	

	
	   IF (ordStatus = 'failed') THEN
			SET checkStatus = 'pending';

		END IF;
	
	SELECT  `id`, `service_id`, `fee`, `tax` INTO  pk_id, ord_service_id, ord_fee, ord_tax
			FROM `pan_txns`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = checkStatus;
			
	set totalAmount = ROUND( ord_fee + ord_tax, 2);
        IF (pk_id != '') THEN
 
            SELECT `id` INTO srv_id
                FROM `user_services`
                WHERE `id` = serviceId;
				
               
		if(srv_id != '') THEN
                    set tr_narration_str = CONCAT(totalAmount , ' credited against ', orderRefId);
                    
		 SELECT `id`, `service_account_number`, `transaction_amount` INTO srv_id, srv_acc_number, srv_txn_amount
                FROM `user_services`
                WHERE `id` = serviceId;
				
					INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_fee`, `tr_tax`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`, `closing_balance`, `service_id`, `created_at`)
                      VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, 0, CONCAT('+',totalAmount), ord_fee, ord_tax , NOW(), 'cr', 'pancard_fund_refund', tr_narration_str  , utr, srv_txn_amount + totalAmount, ord_service_id, NOW());
			
			UPDATE user_services SET transaction_amount  = transaction_amount + totalAmount    WHERE  `id` = serviceId;
		
			UPDATE `pan_txns` SET `status` = ordStatus,   failed_message = errorDescription, updated_at = NOW() WHERE  `order_ref_id` = orderRefId AND `id` = pk_id;
						
			set flag = 1;
                      IF (flag) THEN
                           COMMIT;
                          set message = 'Payment balance Credited successfully';
                     ELSE
                    ROLLBACK;
                        set message = 'Query Error';
                    END IF;
                 ELSE
              
                 set message = CONCAT('User Service account not found ' );

                END IF;


        ELSE
		
        set message = 'Allready amount refunded';
		
        END IF;
	END IF;
	
SET AUTOCOMMIT = true;
	SELECT JSON_MERGE(JSON_OBJECT('status', flag), JSON_OBJECT( 'message', message )) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `rechargeCommissionCreditTransaction`(IN `userId` INT, IN `serviceId` VARCHAR(100), IN `txnIdCr` VARCHAR(100), IN `txnRefIdCr` VARCHAR(100), IN `trIdentifiers` VARCHAR(100), IN `commission` DOUBLE(18,2), IN `finalAmount` DOUBLE(18,2), IN `tds` DOUBLE(18,2), IN `gst` DOUBLE(18,2), IN `idString` VARCHAR(100), OUT `data` JSON)
BEGIN
DECLARE trn_pk_id int (11);
DECLARE user_account_number VARCHAR(250);
DECLARE tr_narration_str VARCHAR(250);
DECLARE service_trn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE user_closing_balance DOUBLE(18, 2);
DECLARE user_services_pk_id int (11);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE message VARCHAR(200);
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET flag = 0;
	select id into trn_pk_id from transactions where user_id = userId AND txn_ref_id = txnRefIdCr limit 1;
	IF(trn_pk_id is NULL) THEN
		
		SELECT transaction_amount, account_number, id INTO service_trn_amount, user_account_number, user_services_pk_id FROM `users`
		WHERE `id` = userId   AND `is_active` = '1';
        IF(user_services_pk_id IS NOT NULL) THEN
			
			UPDATE `recharges` SET is_commission_credited = '1', commission_credited_at = now(), commission_ref_id = txnRefIdCr WHERE id= idString;
			UPDATE `users` SET transaction_amount = service_trn_amount + finalAmount WHERE `id` = user_services_pk_id AND `is_active` = '1';
			SET user_closing_balance = service_trn_amount  + finalAmount;
			SET tr_narration_str = CONCAT(finalAmount , ' credited to main wallet against ', txnRefIdCr);
		         
                        INSERT INTO recharge_commissions( user_id, commission_ref_id, c_amount, tds, gst)
				VALUES (userId, txnRefIdCr, commission, tds, gst);
			
			INSERT INTO transactions (user_id, txn_id,txn_ref_id, account_number, tr_total_amount , tr_amount, tr_type, tr_date,tr_identifiers,
                             tr_narration, closing_balance, service_id)
				VALUES (userId, txnIdCr,txnRefIdCr, user_account_number, CONCAT('+', finalAmount), finalAmount, 'cr', now(),trIdentifiers,
				tr_narration_str, user_closing_balance , serviceId );
			SET flag = 1;
            IF (flag) THEN
                COMMIT;
                SET message = 'Amount credit successfully.';
            ELSE
                ROLLBACK;
                SET message = 'DB action failed.';
            END IF;
		ELSE
			SET message = 'No Recharge transaction found for credit.';
		END IF;
	ELSE
		SET message = 'Commission allready credited.';
	END IF;
		SELECT JSON_MERGE(JSON_OBJECT('status' , flag), JSON_OBJECT('message' , message)) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `rechargestatusUpdate`(IN `orderRefId` VARCHAR(250), IN `userId` INT, IN `serviceId` INT, IN `ordStatus` VARCHAR(50), IN `txnId` VARCHAR(50), IN `errorDescription` VARCHAR(255), IN `statusCode` VARCHAR(50), IN `utr` VARCHAR(200), OUT `data` JSON)
BEGIN
DECLARE srv_id INT DEFAULT 0;
DECLARE srv_acc_number VARCHAR(150) DEFAULT NULL;
DECLARE ord_service_id VARCHAR(250) ;
DECLARE srv_txn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE pk_id INT DEFAULT 0;
DECLARE totalAmount  DOUBLE(18, 2) DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE ser_locked_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE tr_narration_str varchar(200);
	SET AUTOCOMMIT = 0;
	START TRANSACTION;
    set flag = 0;
	
	
	IF ordStatus = 'failed' THEN
		SELECT `amount`, `id`, `service_id` INTO ord_amount, pk_id, ord_service_id
			FROM `recharges`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = 'processing' AND `cron_status` = '1';
	    set totalAmount = ord_amount;	
        IF (pk_id != '') THEN
            SELECT `id` INTO srv_id
                FROM `user_services`
                WHERE `id` = serviceId;
				
		if(srv_id != '') THEN
                    set tr_narration_str = CONCAT(totalAmount , ' credited against ', orderRefId);
		 SELECT `id`, `account_number`, `transaction_amount` INTO srv_id, srv_acc_number, srv_txn_amount
                FROM `users`
                WHERE `id` = userId;
					INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`,`opening_balance`, `closing_balance`, `service_id`, `created_at`)
                      VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, ord_amount, CONCAT('+',totalAmount) , NOW(), 'cr', 'recharge_refund', tr_narration_str  , utr,srv_txn_amount,(SELECT  `transaction_amount`
                FROM `users`
                WHERE `id` = userId ) + totalAmount, ord_service_id, NOW());
			
			UPDATE users SET transaction_amount   = transaction_amount + totalAmount    WHERE  `id` = userId;
		
			UPDATE `recharges` SET `status` = ordStatus, status_code = statusCode,  failed_message = errorDescription, updated_at = NOW() WHERE  `order_ref_id` = orderRefId AND `id` = pk_id;
			set flag = 1;
                      IF (flag) THEN
                           COMMIT;
                          set message = 'Payment balance Credited successfully';
                     ELSE
                    ROLLBACK;
                        set message = 'Query Error';
                    END IF;
                 ELSE
                 set message = CONCAT('User Service account not found ' );
                END IF;
        ELSE
		
        set message = 'Allready amount refunded';
		
        END IF;
	END IF;
	IF ordStatus = 'reversed' THEN
		SELECT `amount`, `id`, `service_id` INTO ord_amount, pk_id, ord_service_id
			FROM `recharges`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = 'processed' AND `cron_status` = '1';
	    set totalAmount = ord_amount;	
        IF (pk_id != '') THEN
            SELECT `id` INTO srv_id
                FROM `user_services`
                WHERE `id` = serviceId;
				
		if(srv_id != '') THEN
                    set tr_narration_str = CONCAT(totalAmount , ' credited against ', orderRefId);
		 SELECT `id`, `account_number`, `transaction_amount` INTO srv_id, srv_acc_number, srv_txn_amount
                FROM `users`
                WHERE `id` = userId;
				
					INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`,`opening_balance`, `closing_balance`, `service_id`, `created_at`)
                      VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, ord_amount, CONCAT('+',totalAmount) , NOW(), 'cr', 'recharge_reversed', tr_narration_str  , utr,srv_txn_amount,(SELECT  `transaction_amount`
                FROM `users`
                WHERE `id` = userId ) + totalAmount, ord_service_id, NOW());
			
			UPDATE users SET transaction_amount   = transaction_amount + totalAmount    WHERE  `id` = userId;
		
			UPDATE `recharges` SET `status` = 'reversed', status_code = statusCode,  txt_2 = errorDescription, updated_at = NOW() WHERE  `order_ref_id` = orderRefId AND `id` = pk_id;
			set flag = 1;
                      IF (flag) THEN
                           COMMIT;
                          set message = 'Payment balance Credited successfully';
                     ELSE
                    ROLLBACK;
                        set message = 'Query Error';
                    END IF;
                 ELSE
                 set message = CONCAT('User Service account not found ' );
                END IF;
        ELSE
		
        set message = 'Status already updated';
		
        END IF;
	END IF;
	
		IF ordStatus = 'dispute' THEN
		SELECT `amount`, `id`, `service_id` INTO ord_amount, pk_id, ord_service_id
			FROM `recharges`
			WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND `status` = 'failed' AND `cron_status` = '1';
	    set totalAmount = ord_amount;	
        IF (pk_id != '') THEN
            SELECT `id` INTO srv_id
                FROM `user_services`
                WHERE `id` = serviceId;
				
		if(srv_id != '') THEN
                    set tr_narration_str = CONCAT(totalAmount , ' debited against ', orderRefId);
		 SELECT `id`, `account_number`, `transaction_amount` INTO srv_id, srv_acc_number, srv_txn_amount
                FROM `users`
                WHERE `id` = userId;
				
					INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`,`opening_balance`, `closing_balance`, `service_id`, `created_at`)
                      VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, ord_amount, CONCAT('-',totalAmount) , NOW(), 'dr', 'recharge_dsuccess', tr_narration_str  , utr,srv_txn_amount,(SELECT  `transaction_amount`
                FROM `users`
                WHERE `id` = userId ) - totalAmount, ord_service_id, NOW());
			
			UPDATE users SET transaction_amount   = transaction_amount - totalAmount    WHERE  `id` = userId;
		
			UPDATE `recharges` SET `status` = 'processed', status_code = statusCode,  txt_2 = errorDescription, updated_at = NOW() WHERE  `order_ref_id` = orderRefId AND `id` = pk_id;
			set flag = 1;
                      IF (flag) THEN
                           COMMIT;
                          set message = 'Payment balance Credited successfully';
                     ELSE
                    ROLLBACK;
                        set message = 'Query Error';
                    END IF;
                 ELSE
                 set message = CONCAT('User Service account not found ' );
                END IF;
        ELSE
		
        set message = 'Status already updated';
		
        END IF;
	END IF;
	SET AUTOCOMMIT = true;
	SELECT JSON_MERGE(JSON_OBJECT('status', flag), JSON_OBJECT( 'message', message )) INTO data;
END$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`root`@`localhost` PROCEDURE `validationStatusUpdate`(IN `orderRefId` VARCHAR(250), IN `userId` INT, IN `serviceId` INT, IN `ordStatus` VARCHAR(50), IN `txnId` VARCHAR(50), IN `errorDescription` VARCHAR(255), IN `statusCode` VARCHAR(50), IN `utr` VARCHAR(200), IN `trIdentifiers` VARCHAR(200), OUT `outData` JSON)
BEGIN
DECLARE srv_id INT DEFAULT 0;
DECLARE srv_acc_number VARCHAR(150) DEFAULT NULL;
DECLARE ord_service_id VARCHAR(250) ;
DECLARE srv_txn_amount DOUBLE(18, 2) DEFAULT 0;
DECLARE ord_amount DOUBLE(18, 2);
DECLARE ord_fee DOUBLE(18, 2);
DECLARE ord_tax DOUBLE(18, 2);
DECLARE pk_id INT DEFAULT 0;
DECLARE totalAmount  DOUBLE(18, 2) DEFAULT 0;
DECLARE message VARCHAR(200);
DECLARE flag VARCHAR(25) DEFAULT 0;
DECLARE tr_narration_str varchar(200);

SET AUTOCOMMIT = 0;

START TRANSACTION;

set flag = 0;

IF ordStatus = 'failed' THEN

    SELECT `id`, `service_id` INTO pk_id, ord_service_id FROM `validations` 
        WHERE `user_id` = userId AND `order_ref_id` = orderRefId AND status = 'pending';

    IF (pk_id != '') THEN

        SELECT `tr_amount`, `tr_fee`, `tr_tax` INTO ord_amount, ord_fee, ord_tax FROM `transactions`
					WHERE `user_id` = userId AND `txn_ref_id` = orderRefId limit 1;
			
		set totalAmount = ROUND(ord_amount + ord_fee + ord_tax, 2);	
	
        SELECT `id` INTO srv_id FROM `user_services` 
            WHERE `id` = serviceId AND `is_active` = '1';
				
		if(srv_id != '') THEN
            
            set tr_narration_str = CONCAT(totalAmount , ' credited against ', orderRefId);

            SELECT  `service_account_number`, `transaction_amount` INTO srv_acc_number, srv_txn_amount FROM `user_services`
                WHERE `id` = serviceId;

            INSERT INTO `transactions`(`id`, `txn_id`, `txn_ref_id`, `account_number`, `user_id`, `tr_amount`, `tr_total_amount`, `tr_fee`, `tr_tax`, `tr_date`, `tr_type`, `tr_identifiers`, `tr_narration`, `tr_reference`, `closing_balance`, `service_id`, `created_at`)
                VALUES (NULL, txnId, orderRefId, srv_acc_number, userId, ord_amount, CONCAT('+',totalAmount) , ord_fee, ord_tax, NOW(), 'cr', trIdentifiers, tr_narration_str  , utr, (SELECT  `transaction_amount` 
                FROM `user_services` WHERE `id` = serviceId ) + totalAmount, ord_service_id, NOW());
			
			UPDATE `user_services` SET `transaction_amount`  = (`transaction_amount` + totalAmount) WHERE `id` = serviceId;
		
			UPDATE `validations` SET `status` = ordStatus, `failed_message` = errorDescription, `updated_at` = NOW() WHERE `id` = pk_id;

			set flag = 1;

            IF (flag) THEN
                COMMIT;
                set message = 'Payment balance Credited successfully';
            ELSE
                ROLLBACK;
                set message = 'Query Error';
            END IF;

        ELSE
              
            set message = CONCAT('User Service account not found ' );

        END IF;

    ELSE
		
        set message = 'Allready amount refunded';
		
    END IF;

END IF;

SET AUTOCOMMIT = true;

SELECT JSON_MERGE(
        JSON_OBJECT('status', flag), 
        JSON_OBJECT( 'message', message )
    ) INTO outData;

END$$
DELIMITER ;
