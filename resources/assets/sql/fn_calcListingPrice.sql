/**
USAGE:
	UPDATE
		products
	SET
		listing_price=fn_calcListingPrice(Cost,Sell,RRP,QTY)
	WHERE
		listing_price<1
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
    DECLARE RESULT double;

    SET SHIPPING=25;
	SET TAX=0.1;
	SET SALE_COST=0.1;
	SET MARGIN=0.15;
    
    SET RESULT=cost+SHIPPING+(cost*TAX)+(cost*SALE_COST)+(cost*MARGIN);
    
    IF RESULT>sell THEN
		SET RESULT=sell;
	END IF;
    
 RETURN (RESULT);
END
