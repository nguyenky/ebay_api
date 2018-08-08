/**
USAGE:
	UPDATE
		products
	SET
		listing_price=fn_calcListingPrice(Cost,Sell,RRP,QTY),
		updated_at=NOW()
	WHERE
		listingID IS NOT NULL
	;
*/
DROP FUNCTION IF EXISTS fn_calcListingPrice;
DELIMITER $$
CREATE FUNCTION fn_calcListingPrice(cost double, sell double, rrp double, qty int) RETURNS double
    DETERMINISTIC
BEGIN
    DECLARE SHIPPING double;
    DECLARE TAX double;
    DECLARE SALE_COST double;
    DECLARE MARGIN double;
    
    DECLARE COST_MARGIN double;
    
    DECLARE RESULT double;

    SET SHIPPING=27;
	SET TAX=0.1;
	SET SALE_COST=0.1;
	SET MARGIN=0.2;

	SET COST_MARGIN=cost+(cost*MARGIN);
    
    SET RESULT=COST_MARGIN+SHIPPING+(COST_MARGIN*TAX)+(COST_MARGIN*SALE_COST);
    
    IF RESULT>sell THEN
		SET RESULT=sell+SHIPPING;
	END IF;
    
 RETURN (RESULT);
END
