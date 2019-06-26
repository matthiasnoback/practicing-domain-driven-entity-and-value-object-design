# About a sales invoice

A sales invoice is a financial document we create for one of our customers. It has a number of lines, each with:
 
- A product (from our catalogue)
- A quantity (with a given decimal precision)
- A tariff, which is the price per piece
- A VAT code
- A discount percentage

Using this information we can calculate the line amount, and the total amount of the invoice.

When the sales invoice is first created, it won't have any lines. We keep adding lines to add, until we are happy about the result. Then we finalize the invoice. After finalizing it, you can't change _any part_ of the invoice anymore. It's now a document that gets shared with the customer, and we wouldn't want to accidentally have a discrepancy between the customer's version and ours.

If we're not happy about an invoice, or the customer cancels their agreement with us, we'd want to be able to cancel the invoice itself too.

We support invoices in foreign currencies. If an invoice is in a foreign currency (something other than EUR), we also calculate the amounts in our own currency (so-called "ledger currency"), so our own bookkeeping can still be done in EUR). To convert between currencies, you need an exchange rate, which is determined based on the invoice date, and also has to show up on the invoice itself, since it's part of the agreement between us and the customer.    
