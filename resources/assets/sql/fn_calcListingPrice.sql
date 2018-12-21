# noinspection SqlNoDataSourceInspectionForFile

/**
USAGE SELECT:
SELECT
	p.sku,
	p.cost,
    (((fn_calcListingPrice(p.cost,c.price)-27.5-(0.2*p.cost)-p.cost)/p.cost)*100) margin,
    27.5 shipping,
    (0.2*p.cost) sale_cost,
    fn_calcListingPrice(p.cost,c.price) price,
    c.price cheapest_competitor
FROM
	products p
    INNER JOIN ebay_details ed ON p.id=ed.product_id
    INNER JOIN
	(
		SELECT
			sku,
			MIN(price) price
		FROM
			competitor_items c
		WHERE
			latest=1
		GROUP BY
			sku
	) as c ON p.sku=c.sku
LIMIT
	5000
;

USAGE UPDATE:
	UPDATE
		products
	SET
		listing_price=fn_calcListingPrice(cost,competitor),
		updated_at=NOW()
	WHERE
		listingid IS NOT NULL
	;
*/
DROP FUNCTION IF EXISTS fn_calcListingPrice;
DELIMITER $$
CREATE FUNCTION fn_calcListingPrice(cost double, competitor double) RETURNS double
    DETERMINISTIC
BEGIN
  DECLARE SHIPPING double;
  DECLARE TAX double;
  DECLARE SALE_COST double;
  DECLARE MARGIN double;

  DECLARE COST_MARGIN double;

  DECLARE RESULT double;

  SET SHIPPING=27.5;
	SET TAX=0.1;
	SET SALE_COST=0.1;
	SET MARGIN=0.25;

	SET COST_MARGIN=cost+(cost*MARGIN);
  SET RESULT=COST_MARGIN+SHIPPING+(COST_MARGIN*TAX)+(COST_MARGIN*SALE_COST);

  #If we can get more money by beating our competition by 5% and still get 10% margin. Let's do that.
  IF competitor>0 AND 0.95*competitor>RESULT THEN
    SET RESULT=0.95*competitor;
  END IF;

  RETURN (RESULT);
END
