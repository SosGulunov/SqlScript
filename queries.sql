-- a

SELECT name
FROM clients
WHERE id NOT IN (
    SELECT customer_id
    FROM orders
    WHERE order_date >= DATE('now', '-7 days')
);

-- b

SELECT clients.name
FROM clients
JOIN orders ON clients.id = orders.customer_id
GROUP BY clients.id
ORDER BY COUNT(orders.id) DESC
LIMIT 5;

-- c

SELECT clients.name
FROM clients
JOIN orders ON clients.id = orders.customer_id
GROUP BY clients.id
ORDER BY SUM(orders.price) DESC  
LIMIT 10;

-- d
SELECT merchandise.name
FROM merchandise
WHERE id NOT IN (
    SELECT item_id
    FROM orders
    WHERE status = 'complete'
);


-- Индексы:
CREATE INDEX idx_orders_customer_id ON orders(customer_id);

CREATE INDEX idx_orders_item_id ON orders(item_id);

CREATE INDEX idx_orders_order_date ON orders(order_date);

CREATE INDEX idx_orders_status ON orders(status);
