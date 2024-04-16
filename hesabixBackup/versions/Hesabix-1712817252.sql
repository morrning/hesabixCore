CREATE TABLE user_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, token_id VARCHAR(255) DEFAULT NULL, INDEX IDX_BDF55A63A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, date_register VARCHAR(50) NOT NULL, mobile VARCHAR(15) DEFAULT NULL, verify_code VARCHAR(255) DEFAULT NULL, active TINYINT(1) DEFAULT NULL, verify_code_time VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE business (id INT AUTO_INCREMENT NOT NULL, money_id INT DEFAULT NULL, owner_id INT NOT NULL, wallet_match_bank_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, legal_name VARCHAR(255) NOT NULL, field VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, shenasemeli VARCHAR(255) DEFAULT NULL, codeeghtesadi VARCHAR(255) DEFAULT NULL, shomaresabt VARCHAR(255) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, ostan VARCHAR(255) DEFAULT NULL, shahrestan VARCHAR(255) DEFAULT NULL, postalcode VARCHAR(255) DEFAULT NULL, tel VARCHAR(12) DEFAULT NULL, mobile VARCHAR(12) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, wesite VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, maliyatafzode VARCHAR(255) NOT NULL, date_submit VARCHAR(255) NOT NULL, person_code BIGINT DEFAULT NULL, bank_code BIGINT DEFAULT NULL, receive_code VARCHAR(255) DEFAULT NULL, accounting_code VARCHAR(255) DEFAULT NULL, commodity_code BIGINT DEFAULT NULL, salary_code VARCHAR(255) DEFAULT NULL, cashdesk_code VARCHAR(255) DEFAULT NULL, zarinpal_code VARCHAR(255) DEFAULT NULL, store_online TINYINT(1) DEFAULT NULL, store_username VARCHAR(255) DEFAULT NULL, sms_charge VARCHAR(255) DEFAULT NULL, shortlinks TINYINT(1) DEFAULT NULL, wallet_enable TINYINT(1) DEFAULT NULL, storeroom_code VARCHAR(255) DEFAULT NULL, archive_size VARCHAR(255) DEFAULT NULL, INDEX IDX_8D36E38BF29332C (money_id), INDEX IDX_8D36E387E3C61F9 (owner_id), INDEX IDX_8D36E38574F80DE (wallet_match_bank_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE guide_content (id INT AUTO_INCREMENT NOT NULL, submiter_id INT NOT NULL, cat VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT DEFAULT NULL, date_submit VARCHAR(25) NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_CAD3AA81A2251D63 (submiter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE stack_content (id INT AUTO_INCREMENT NOT NULL, submitter_id INT NOT NULL, cat_id INT NOT NULL, upper_id INT DEFAULT NULL, date_submit VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, views BIGINT NOT NULL, url VARCHAR(255) NOT NULL, INDEX IDX_B5150B0C919E5513 (submitter_id), INDEX IDX_B5150B0CE6ADA943 (cat_id), INDEX IDX_B5150B0C6F3C117F (upper_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE blog_post (id INT AUTO_INCREMENT NOT NULL, submitter_id INT NOT NULL, cat_id INT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, date_submit VARCHAR(50) NOT NULL, views BIGINT NOT NULL, img VARCHAR(255) DEFAULT NULL, url VARCHAR(255) NOT NULL, intero LONGTEXT NOT NULL, keywords VARCHAR(255) DEFAULT NULL, INDEX IDX_BA5AE01D919E5513 (submitter_id), INDEX IDX_BA5AE01DE6ADA943 (cat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE blog_comment (id INT AUTO_INCREMENT NOT NULL, post_id INT NOT NULL, date_submit VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, publish TINYINT(1) DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, website VARCHAR(255) DEFAULT NULL, INDEX IDX_7882EFEF4B89032C (post_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, bid_id INT DEFAULT NULL, doc_id INT DEFAULT NULL, date_submit VARCHAR(255) NOT NULL, part VARCHAR(255) NOT NULL, des VARCHAR(255) NOT NULL, ipaddress VARCHAR(255) DEFAULT NULL, INDEX IDX_8F3F68C5A76ED395 (user_id), INDEX IDX_8F3F68C54D9866B8 (bid_id), INDEX IDX_8F3F68C5895648BC (doc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE permission (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, bid_id INT NOT NULL, owner TINYINT(1) DEFAULT NULL, settings TINYINT(1) DEFAULT NULL, person TINYINT(1) DEFAULT NULL, commodity TINYINT(1) DEFAULT NULL, getpay TINYINT(1) DEFAULT NULL, banks TINYINT(1) DEFAULT NULL, bank_transfer TINYINT(1) DEFAULT NULL, buy TINYINT(1) DEFAULT NULL, sell TINYINT(1) DEFAULT NULL, cost TINYINT(1) DEFAULT NULL, income TINYINT(1) DEFAULT NULL, accounting TINYINT(1) DEFAULT NULL, report TINYINT(1) DEFAULT NULL, log TINYINT(1) DEFAULT NULL, permission TINYINT(1) DEFAULT NULL, salary TINYINT(1) DEFAULT NULL, cashdesk TINYINT(1) DEFAULT NULL, plug_noghre_admin TINYINT(1) DEFAULT NULL, plug_noghre_sell TINYINT(1) DEFAULT NULL, plug_ccadmin TINYINT(1) DEFAULT NULL, store TINYINT(1) DEFAULT NULL, wallet TINYINT(1) DEFAULT NULL, archive_upload TINYINT(1) DEFAULT NULL, archive_mod TINYINT(1) DEFAULT NULL, archive_delete TINYINT(1) DEFAULT NULL, shareholder TINYINT(1) DEFAULT NULL, archive_view TINYINT(1) DEFAULT NULL, cheque TINYINT(1) DEFAULT NULL, INDEX IDX_E04992AAA76ED395 (user_id), INDEX IDX_E04992AA4D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE hesabdari_doc (id INT AUTO_INCREMENT NOT NULL, bid_id INT DEFAULT NULL, submitter_id INT NOT NULL, year_id INT NOT NULL, money_id INT NOT NULL, wallet_transaction_id INT DEFAULT NULL, date_submit VARCHAR(50) NOT NULL, date VARCHAR(50) NOT NULL, type VARCHAR(255) DEFAULT NULL, code BIGINT NOT NULL, des VARCHAR(255) DEFAULT NULL, amount VARCHAR(255) DEFAULT NULL, mdate VARCHAR(255) DEFAULT NULL, plugin VARCHAR(255) DEFAULT NULL, ref_data VARCHAR(255) DEFAULT NULL, shortlink VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, temp_status LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', INDEX IDX_81C3CD534D9866B8 (bid_id), INDEX IDX_81C3CD53919E5513 (submitter_id), INDEX IDX_81C3CD5340C1FEA7 (year_id), INDEX IDX_81C3CD53BF29332C (money_id), INDEX IDX_81C3CD53924C1837 (wallet_transaction_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE hesabdari_doc_hesabdari_doc (hesabdari_doc_source INT NOT NULL, hesabdari_doc_target INT NOT NULL, INDEX IDX_BE675746E2A225E5 (hesabdari_doc_source), INDEX IDX_BE675746FB47756A (hesabdari_doc_target), PRIMARY KEY(hesabdari_doc_source, hesabdari_doc_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE support (id INT AUTO_INCREMENT NOT NULL, submitter_id INT NOT NULL, main VARCHAR(255) DEFAULT NULL, date_submit VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, state VARCHAR(255) NOT NULL, INDEX IDX_8004EBA5919E5513 (submitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, bid_id INT DEFAULT NULL, url LONGTEXT DEFAULT NULL, icon VARCHAR(255) DEFAULT NULL, message VARCHAR(255) DEFAULT NULL, date_submit VARCHAR(255) NOT NULL, viewed TINYINT(1) NOT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CA4D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE email_history (id INT AUTO_INCREMENT NOT NULL, submitter_id INT DEFAULT NULL, date_submit VARCHAR(255) NOT NULL, title VARCHAR(255) DEFAULT NULL, body LONGTEXT DEFAULT NULL, INDEX IDX_9A7A1884919E5513 (submitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE smspays (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, submitter_id INT NOT NULL, date_submit VARCHAR(255) NOT NULL, price VARCHAR(255) NOT NULL, des VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, ref_id VARCHAR(255) DEFAULT NULL, card_pan VARCHAR(255) DEFAULT NULL, verify_code VARCHAR(255) DEFAULT NULL, gate_pay VARCHAR(255) NOT NULL, INDEX IDX_5F2F70E14D9866B8 (bid_id), INDEX IDX_5F2F70E1919E5513 (submitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE wallet_transaction (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, submitter_id INT DEFAULT NULL, date_submit VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, amount VARCHAR(255) NOT NULL, shaba VARCHAR(255) DEFAULT NULL, bank VARCHAR(255) DEFAULT NULL, card_num VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, des VARCHAR(255) DEFAULT NULL, card_pan VARCHAR(255) DEFAULT NULL, ref_id VARCHAR(255) DEFAULT NULL, verify_code VARCHAR(255) DEFAULT NULL, gate_pay VARCHAR(255) DEFAULT NULL, transaction_id VARCHAR(255) DEFAULT NULL, INDEX IDX_7DAF9724D9866B8 (bid_id), INDEX IDX_7DAF972919E5513 (submitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE storeroom_ticket (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, submitter_id INT NOT NULL, person_id INT DEFAULT NULL, doc_id INT DEFAULT NULL, year_id INT NOT NULL, storeroom_id INT NOT NULL, transfer_type_id INT NOT NULL, date VARCHAR(255) NOT NULL, date_submit VARCHAR(255) NOT NULL, transfer VARCHAR(255) DEFAULT NULL, receiver VARCHAR(255) DEFAULT NULL, code VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, referral VARCHAR(255) DEFAULT NULL, type_string VARCHAR(255) NOT NULL, des VARCHAR(255) DEFAULT NULL, INDEX IDX_9B4CC0F74D9866B8 (bid_id), INDEX IDX_9B4CC0F7919E5513 (submitter_id), INDEX IDX_9B4CC0F7217BBB47 (person_id), INDEX IDX_9B4CC0F7895648BC (doc_id), INDEX IDX_9B4CC0F740C1FEA7 (year_id), INDEX IDX_9B4CC0F7C9330186 (storeroom_id), INDEX IDX_9B4CC0F77AF9FED8 (transfer_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE archive_file (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, submitter_id INT NOT NULL, date_submit VARCHAR(255) NOT NULL, date_mod VARCHAR(255) DEFAULT NULL, filename VARCHAR(255) NOT NULL, cat VARCHAR(255) NOT NULL, file_type VARCHAR(255) NOT NULL, public TINYINT(1) DEFAULT NULL, des VARCHAR(255) DEFAULT NULL, related_doc_type VARCHAR(255) NOT NULL, related_doc_code VARCHAR(255) DEFAULT NULL, file_size VARCHAR(255) DEFAULT NULL, INDEX IDX_BCBAE08B4D9866B8 (bid_id), INDEX IDX_BCBAE08B919E5513 (submitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE archive_orders (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, submitter_id INT NOT NULL, date_submit VARCHAR(255) NOT NULL, order_size VARCHAR(255) NOT NULL, gate_pay VARCHAR(255) DEFAULT NULL, price VARCHAR(255) DEFAULT NULL, verify_code VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, ref_id VARCHAR(255) DEFAULT NULL, card_pan VARCHAR(255) DEFAULT NULL, expire_date VARCHAR(255) DEFAULT NULL, des VARCHAR(255) DEFAULT NULL, month VARCHAR(255) NOT NULL, INDEX IDX_182AE9FB4D9866B8 (bid_id), INDEX IDX_182AE9FB919E5513 (submitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE hook (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, submitter_id INT NOT NULL, url LONGTEXT NOT NULL, password VARCHAR(255) NOT NULL, INDEX IDX_A45843554D9866B8 (bid_id), INDEX IDX_A4584355919E5513 (submitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE cheque (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, submitter_id INT NOT NULL, bank_id INT DEFAULT NULL, person_id INT DEFAULT NULL, ref_id INT DEFAULT NULL, date_submit VARCHAR(50) NOT NULL, type VARCHAR(20) NOT NULL, sayad_num VARCHAR(50) DEFAULT NULL, des VARCHAR(255) DEFAULT NULL, date_stamp VARCHAR(50) NOT NULL, pay_date VARCHAR(50) DEFAULT NULL, number VARCHAR(255) NOT NULL, bank_oncheque VARCHAR(255) NOT NULL, amount VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, locked TINYINT(1) DEFAULT NULL, date VARCHAR(255) DEFAULT NULL, rejected TINYINT(1) DEFAULT NULL, INDEX IDX_A0BBFDE94D9866B8 (bid_id), INDEX IDX_A0BBFDE9919E5513 (submitter_id), INDEX IDX_A0BBFDE911C8FB41 (bank_id), INDEX IDX_A0BBFDE9217BBB47 (person_id), INDEX IDX_A0BBFDE921B741A9 (ref_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE money (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, code BIGINT NOT NULL, nikename VARCHAR(255) NOT NULL, name VARCHAR(255) DEFAULT NULL, tel VARCHAR(12) DEFAULT NULL, mobile VARCHAR(12) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, des VARCHAR(255) DEFAULT NULL, plug_noghre_morsa TINYINT(1) DEFAULT NULL, plug_noghre_hakak TINYINT(1) DEFAULT NULL, plug_noghre_tarash TINYINT(1) DEFAULT NULL, employe TINYINT(1) DEFAULT NULL, plug_noghre_ghalam TINYINT(1) DEFAULT NULL, company VARCHAR(255) DEFAULT NULL, shenasemeli VARCHAR(255) DEFAULT NULL, codeeghtesadi VARCHAR(255) DEFAULT NULL, sabt VARCHAR(255) DEFAULT NULL, keshvar VARCHAR(255) DEFAULT NULL, ostan VARCHAR(255) DEFAULT NULL, shahr VARCHAR(255) DEFAULT NULL, postalcode VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, fax VARCHAR(255) DEFAULT NULL, birthday VARCHAR(255) DEFAULT NULL, speed_access TINYINT(1) DEFAULT NULL, INDEX IDX_34DCD1764D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE year (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, label VARCHAR(255) NOT NULL, head TINYINT(1) DEFAULT NULL, start VARCHAR(255) NOT NULL, end VARCHAR(255) NOT NULL, now VARCHAR(255) DEFAULT NULL, INDEX IDX_BB8273374D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE bank_account (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, card_num VARCHAR(255) DEFAULT NULL, shaba VARCHAR(255) DEFAULT NULL, account_num VARCHAR(255) DEFAULT NULL, owner VARCHAR(255) DEFAULT NULL, shobe VARCHAR(255) DEFAULT NULL, pos_num VARCHAR(255) DEFAULT NULL, des VARCHAR(255) DEFAULT NULL, mobile_internet_bank VARCHAR(25) DEFAULT NULL, code VARCHAR(255) NOT NULL, balance VARCHAR(255) DEFAULT NULL, INDEX IDX_53A23E0A4D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE hesabdari_row (id INT AUTO_INCREMENT NOT NULL, doc_id INT NOT NULL, ref_id INT NOT NULL, person_id INT DEFAULT NULL, bank_id INT DEFAULT NULL, bid_id INT NOT NULL, year_id INT NOT NULL, commodity_id INT DEFAULT NULL, salary_id INT DEFAULT NULL, cashdesk_id INT DEFAULT NULL, cheque_id INT DEFAULT NULL, bs VARCHAR(255) NOT NULL, bd VARCHAR(255) NOT NULL, des VARCHAR(255) DEFAULT NULL, commdity_count VARCHAR(255) DEFAULT NULL, referral VARCHAR(255) DEFAULT NULL, ref_data VARCHAR(255) DEFAULT NULL, plugin VARCHAR(255) DEFAULT NULL, temp_data LONGTEXT DEFAULT NULL COMMENT '(DC2Type:array)', INDEX IDX_83B2C6EC895648BC (doc_id), INDEX IDX_83B2C6EC21B741A9 (ref_id), INDEX IDX_83B2C6EC217BBB47 (person_id), INDEX IDX_83B2C6EC11C8FB41 (bank_id), INDEX IDX_83B2C6EC4D9866B8 (bid_id), INDEX IDX_83B2C6EC40C1FEA7 (year_id), INDEX IDX_83B2C6ECB4ACC212 (commodity_id), INDEX IDX_83B2C6ECB0FDF16E (salary_id), INDEX IDX_83B2C6ECBA216AA5 (cashdesk_id), INDEX IDX_83B2C6EC3DD3DB4B (cheque_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE salary (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, name VARCHAR(255) NOT NULL, des LONGTEXT DEFAULT NULL, code VARCHAR(255) NOT NULL, balance VARCHAR(255) DEFAULT NULL, INDEX IDX_9413BB714D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE cashdesk (id INT AUTO_INCREMENT NOT NULL, bid_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, des LONGTEXT DEFAULT NULL, code VARCHAR(255) NOT NULL, balance VARCHAR(255) DEFAULT NULL, INDEX IDX_165987F94D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE plugin (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, submitter_id INT NOT NULL, date_expire VARCHAR(255) DEFAULT NULL, gate_pay VARCHAR(255) DEFAULT NULL, price VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, des VARCHAR(255) DEFAULT NULL, verify_code VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, date_submit VARCHAR(255) NOT NULL, ref_id VARCHAR(255) DEFAULT NULL, card_pan VARCHAR(255) DEFAULT NULL, INDEX IDX_E96E27944D9866B8 (bid_id), INDEX IDX_E96E2794919E5513 (submitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE plug_noghre_order (id INT AUTO_INCREMENT NOT NULL, doc_id INT NOT NULL, bid_id INT NOT NULL, morsa_id INT DEFAULT NULL, tarash_id INT DEFAULT NULL, hakak_id INT DEFAULT NULL, ghalam_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, delivery_date VARCHAR(255) DEFAULT NULL, place VARCHAR(255) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, negin VARCHAR(255) DEFAULT NULL, noghre_amount VARCHAR(255) DEFAULT NULL, negin_fee VARCHAR(255) DEFAULT NULL, ring_model VARCHAR(255) DEFAULT NULL, ring_size VARCHAR(255) DEFAULT NULL, des VARCHAR(255) DEFAULT NULL, noghre_fee VARCHAR(255) DEFAULT NULL, INDEX IDX_EEEE085E895648BC (doc_id), INDEX IDX_EEEE085E4D9866B8 (bid_id), INDEX IDX_EEEE085EB130EC9E (morsa_id), INDEX IDX_EEEE085E36B8627E (tarash_id), INDEX IDX_EEEE085EF8ABEE72 (hakak_id), INDEX IDX_EEEE085E7BECA6BC (ghalam_id), INDEX IDX_EEEE085E9395C3F3 (customer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE smssettings (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, send_after_sell TINYINT(1) DEFAULT NULL, send_after_sell_pay_online TINYINT(1) DEFAULT NULL, send_after_buy TINYINT(1) DEFAULT NULL, send_after_buy_to_user TINYINT(1) DEFAULT NULL, INDEX IDX_61178A624D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE commodity_drop (id INT AUTO_INCREMENT NOT NULL, bid_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, udprice VARCHAR(255) DEFAULT NULL, udprice_percent VARCHAR(255) DEFAULT NULL, can_edit TINYINT(1) DEFAULT NULL, INDEX IDX_14E674574D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE storeroom (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, name VARCHAR(255) NOT NULL, manager VARCHAR(255) DEFAULT NULL, adr VARCHAR(255) DEFAULT NULL, tel VARCHAR(255) DEFAULT NULL, active TINYINT(1) DEFAULT NULL, INDEX IDX_3E2092A84D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE storeroom_item (id INT AUTO_INCREMENT NOT NULL, ticket_id INT NOT NULL, commodity_id INT NOT NULL, bid_id INT NOT NULL, storeroom_id INT NOT NULL, type VARCHAR(255) NOT NULL, count VARCHAR(255) NOT NULL, des VARCHAR(255) DEFAULT NULL, referal VARCHAR(255) DEFAULT NULL, INDEX IDX_6CA8F5E0700047D2 (ticket_id), INDEX IDX_6CA8F5E0B4ACC212 (commodity_id), INDEX IDX_6CA8F5E04D9866B8 (bid_id), INDEX IDX_6CA8F5E0C9330186 (storeroom_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE shareholder (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, person_id INT NOT NULL, percent INT NOT NULL, INDEX IDX_D5FE68CC4D9866B8 (bid_id), INDEX IDX_D5FE68CC217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE stack_cat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE blog_cat (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE storeroom_transfer_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE hesabdari_table (id INT AUTO_INCREMENT NOT NULL, upper_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, entity VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_40F7185C77153098 (code), INDEX IDX_40F7185C6F3C117F (upper_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE commodity_unit (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE commodity_drop_link (id INT AUTO_INCREMENT NOT NULL, commoditydrop_id INT NOT NULL, commodity_id INT NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_8626B6BDC15B0809 (commoditydrop_id), INDEX IDX_8626B6BDB4ACC212 (commodity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE change_report (id INT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, date_submit VARCHAR(255) NOT NULL, version VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE plugin_prodect (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, timestamp VARCHAR(255) NOT NULL, timelabel VARCHAR(255) NOT NULL, price VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE commodity_cat (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, upper VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, root TINYINT(1) DEFAULT NULL, INDEX IDX_687F6B14D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE apitoken (id INT AUTO_INCREMENT NOT NULL, bid_id INT DEFAULT NULL, submitter_id INT NOT NULL, token VARCHAR(255) NOT NULL, date_expire VARCHAR(255) DEFAULT NULL, INDEX IDX_23E5A7D34D9866B8 (bid_id), INDEX IDX_23E5A7D3919E5513 (submitter_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, active_send_sms TINYINT(1) DEFAULT NULL, zarinpal_merchant VARCHAR(255) DEFAULT NULL, app_site VARCHAR(255) DEFAULT NULL, storage_price VARCHAR(255) DEFAULT NULL, site_keywords LONGTEXT DEFAULT NULL, discription VARCHAR(255) DEFAULT NULL, scripts LONGTEXT DEFAULT NULL, footer_scripts LONGTEXT DEFAULT NULL, footer LONGTEXT DEFAULT NULL, active_sms_panel VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE registry (id INT AUTO_INCREMENT NOT NULL, root VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, value_of_key VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE statment (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) DEFAULT NULL, body LONGTEXT NOT NULL, date_submit VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE commodity (id INT AUTO_INCREMENT NOT NULL, unit_id INT NOT NULL, bid_id INT NOT NULL, cat_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, des VARCHAR(255) DEFAULT NULL, code BIGINT NOT NULL, price_buy VARCHAR(255) DEFAULT NULL, price_sell VARCHAR(255) DEFAULT NULL, khadamat TINYINT(1) DEFAULT NULL, order_point VARCHAR(255) DEFAULT NULL, commodity_count_check TINYINT(1) DEFAULT NULL, min_order_count VARCHAR(255) DEFAULT NULL, day_loading VARCHAR(255) DEFAULT NULL, speed_access TINYINT(1) DEFAULT NULL, INDEX IDX_5E8D2F74F8BD700D (unit_id), INDEX IDX_5E8D2F744D9866B8 (bid_id), INDEX IDX_5E8D2F74E6ADA943 (cat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE pay_info_temp (id INT AUTO_INCREMENT NOT NULL, bid_id INT NOT NULL, doc_id INT DEFAULT NULL, date_submit VARCHAR(255) NOT NULL, des VARCHAR(255) DEFAULT NULL, price VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, verify_code VARCHAR(255) DEFAULT NULL, gate_pay VARCHAR(255) NOT NULL, ref_id VARCHAR(255) DEFAULT NULL, card_pan VARCHAR(255) DEFAULT NULL, INDEX IDX_7F36E8384D9866B8 (bid_id), INDEX IDX_7F36E838895648BC (doc_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE printer_queue (id INT AUTO_INCREMENT NOT NULL, submitter_id INT NOT NULL, bid_id INT DEFAULT NULL, date_submit VARCHAR(255) NOT NULL, pid VARCHAR(255) NOT NULL, view LONGTEXT DEFAULT NULL, INDEX IDX_93F2764B919E5513 (submitter_id), INDEX IDX_93F2764B4D9866B8 (bid_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE apidocument (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, date_submit VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
ALTER TABLE user_token ADD CONSTRAINT FK_BDF55A63A76ED395 FOREIGN KEY (user_id) REFERENCES user (id);
ALTER TABLE business ADD CONSTRAINT FK_8D36E38BF29332C FOREIGN KEY (money_id) REFERENCES money (id);
ALTER TABLE business ADD CONSTRAINT FK_8D36E387E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id);
ALTER TABLE business ADD CONSTRAINT FK_8D36E38574F80DE FOREIGN KEY (wallet_match_bank_id) REFERENCES bank_account (id);
ALTER TABLE guide_content ADD CONSTRAINT FK_CAD3AA81A2251D63 FOREIGN KEY (submiter_id) REFERENCES user (id);
ALTER TABLE stack_content ADD CONSTRAINT FK_B5150B0C919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE stack_content ADD CONSTRAINT FK_B5150B0CE6ADA943 FOREIGN KEY (cat_id) REFERENCES stack_cat (id);
ALTER TABLE stack_content ADD CONSTRAINT FK_B5150B0C6F3C117F FOREIGN KEY (upper_id) REFERENCES stack_content (id);
ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01D919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01DE6ADA943 FOREIGN KEY (cat_id) REFERENCES blog_cat (id);
ALTER TABLE blog_comment ADD CONSTRAINT FK_7882EFEF4B89032C FOREIGN KEY (post_id) REFERENCES blog_post (id);
ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id);
ALTER TABLE log ADD CONSTRAINT FK_8F3F68C54D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5895648BC FOREIGN KEY (doc_id) REFERENCES hesabdari_doc (id);
ALTER TABLE permission ADD CONSTRAINT FK_E04992AAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id);
ALTER TABLE permission ADD CONSTRAINT FK_E04992AA4D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE hesabdari_doc ADD CONSTRAINT FK_81C3CD534D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE hesabdari_doc ADD CONSTRAINT FK_81C3CD53919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE hesabdari_doc ADD CONSTRAINT FK_81C3CD5340C1FEA7 FOREIGN KEY (year_id) REFERENCES year (id);
ALTER TABLE hesabdari_doc ADD CONSTRAINT FK_81C3CD53BF29332C FOREIGN KEY (money_id) REFERENCES money (id);
ALTER TABLE hesabdari_doc ADD CONSTRAINT FK_81C3CD53924C1837 FOREIGN KEY (wallet_transaction_id) REFERENCES wallet_transaction (id);
ALTER TABLE hesabdari_doc_hesabdari_doc ADD CONSTRAINT FK_BE675746E2A225E5 FOREIGN KEY (hesabdari_doc_source) REFERENCES hesabdari_doc (id) ON DELETE CASCADE;
ALTER TABLE hesabdari_doc_hesabdari_doc ADD CONSTRAINT FK_BE675746FB47756A FOREIGN KEY (hesabdari_doc_target) REFERENCES hesabdari_doc (id) ON DELETE CASCADE;
ALTER TABLE support ADD CONSTRAINT FK_8004EBA5919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id);
ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA4D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE email_history ADD CONSTRAINT FK_9A7A1884919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE smspays ADD CONSTRAINT FK_5F2F70E14D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE smspays ADD CONSTRAINT FK_5F2F70E1919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE wallet_transaction ADD CONSTRAINT FK_7DAF9724D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE wallet_transaction ADD CONSTRAINT FK_7DAF972919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE storeroom_ticket ADD CONSTRAINT FK_9B4CC0F74D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE storeroom_ticket ADD CONSTRAINT FK_9B4CC0F7919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE storeroom_ticket ADD CONSTRAINT FK_9B4CC0F7217BBB47 FOREIGN KEY (person_id) REFERENCES person (id);
ALTER TABLE storeroom_ticket ADD CONSTRAINT FK_9B4CC0F7895648BC FOREIGN KEY (doc_id) REFERENCES hesabdari_doc (id);
ALTER TABLE storeroom_ticket ADD CONSTRAINT FK_9B4CC0F740C1FEA7 FOREIGN KEY (year_id) REFERENCES year (id);
ALTER TABLE storeroom_ticket ADD CONSTRAINT FK_9B4CC0F7C9330186 FOREIGN KEY (storeroom_id) REFERENCES storeroom (id);
ALTER TABLE storeroom_ticket ADD CONSTRAINT FK_9B4CC0F77AF9FED8 FOREIGN KEY (transfer_type_id) REFERENCES storeroom_transfer_type (id);
ALTER TABLE archive_file ADD CONSTRAINT FK_BCBAE08B4D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE archive_file ADD CONSTRAINT FK_BCBAE08B919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE archive_orders ADD CONSTRAINT FK_182AE9FB4D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE archive_orders ADD CONSTRAINT FK_182AE9FB919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE hook ADD CONSTRAINT FK_A45843554D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE hook ADD CONSTRAINT FK_A4584355919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE cheque ADD CONSTRAINT FK_A0BBFDE94D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE cheque ADD CONSTRAINT FK_A0BBFDE9919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE cheque ADD CONSTRAINT FK_A0BBFDE911C8FB41 FOREIGN KEY (bank_id) REFERENCES bank_account (id);
ALTER TABLE cheque ADD CONSTRAINT FK_A0BBFDE9217BBB47 FOREIGN KEY (person_id) REFERENCES person (id);
ALTER TABLE cheque ADD CONSTRAINT FK_A0BBFDE921B741A9 FOREIGN KEY (ref_id) REFERENCES hesabdari_table (id);
ALTER TABLE person ADD CONSTRAINT FK_34DCD1764D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE year ADD CONSTRAINT FK_BB8273374D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0A4D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6EC895648BC FOREIGN KEY (doc_id) REFERENCES hesabdari_doc (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6EC21B741A9 FOREIGN KEY (ref_id) REFERENCES hesabdari_table (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6EC217BBB47 FOREIGN KEY (person_id) REFERENCES person (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6EC11C8FB41 FOREIGN KEY (bank_id) REFERENCES bank_account (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6EC4D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6EC40C1FEA7 FOREIGN KEY (year_id) REFERENCES year (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6ECB4ACC212 FOREIGN KEY (commodity_id) REFERENCES commodity (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6ECB0FDF16E FOREIGN KEY (salary_id) REFERENCES salary (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6ECBA216AA5 FOREIGN KEY (cashdesk_id) REFERENCES cashdesk (id);
ALTER TABLE hesabdari_row ADD CONSTRAINT FK_83B2C6EC3DD3DB4B FOREIGN KEY (cheque_id) REFERENCES cheque (id);
ALTER TABLE salary ADD CONSTRAINT FK_9413BB714D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE cashdesk ADD CONSTRAINT FK_165987F94D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE plugin ADD CONSTRAINT FK_E96E27944D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE plugin ADD CONSTRAINT FK_E96E2794919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE plug_noghre_order ADD CONSTRAINT FK_EEEE085E895648BC FOREIGN KEY (doc_id) REFERENCES hesabdari_doc (id);
ALTER TABLE plug_noghre_order ADD CONSTRAINT FK_EEEE085E4D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE plug_noghre_order ADD CONSTRAINT FK_EEEE085EB130EC9E FOREIGN KEY (morsa_id) REFERENCES person (id);
ALTER TABLE plug_noghre_order ADD CONSTRAINT FK_EEEE085E36B8627E FOREIGN KEY (tarash_id) REFERENCES person (id);
ALTER TABLE plug_noghre_order ADD CONSTRAINT FK_EEEE085EF8ABEE72 FOREIGN KEY (hakak_id) REFERENCES person (id);
ALTER TABLE plug_noghre_order ADD CONSTRAINT FK_EEEE085E7BECA6BC FOREIGN KEY (ghalam_id) REFERENCES person (id);
ALTER TABLE plug_noghre_order ADD CONSTRAINT FK_EEEE085E9395C3F3 FOREIGN KEY (customer_id) REFERENCES person (id);
ALTER TABLE smssettings ADD CONSTRAINT FK_61178A624D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE commodity_drop ADD CONSTRAINT FK_14E674574D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE storeroom ADD CONSTRAINT FK_3E2092A84D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE storeroom_item ADD CONSTRAINT FK_6CA8F5E0700047D2 FOREIGN KEY (ticket_id) REFERENCES storeroom_ticket (id);
ALTER TABLE storeroom_item ADD CONSTRAINT FK_6CA8F5E0B4ACC212 FOREIGN KEY (commodity_id) REFERENCES commodity (id);
ALTER TABLE storeroom_item ADD CONSTRAINT FK_6CA8F5E04D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE storeroom_item ADD CONSTRAINT FK_6CA8F5E0C9330186 FOREIGN KEY (storeroom_id) REFERENCES storeroom (id);
ALTER TABLE shareholder ADD CONSTRAINT FK_D5FE68CC4D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE shareholder ADD CONSTRAINT FK_D5FE68CC217BBB47 FOREIGN KEY (person_id) REFERENCES person (id);
ALTER TABLE hesabdari_table ADD CONSTRAINT FK_40F7185C6F3C117F FOREIGN KEY (upper_id) REFERENCES hesabdari_table (id);
ALTER TABLE commodity_drop_link ADD CONSTRAINT FK_8626B6BDC15B0809 FOREIGN KEY (commoditydrop_id) REFERENCES commodity_drop (id);
ALTER TABLE commodity_drop_link ADD CONSTRAINT FK_8626B6BDB4ACC212 FOREIGN KEY (commodity_id) REFERENCES commodity (id);
ALTER TABLE commodity_cat ADD CONSTRAINT FK_687F6B14D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE apitoken ADD CONSTRAINT FK_23E5A7D34D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE apitoken ADD CONSTRAINT FK_23E5A7D3919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE commodity ADD CONSTRAINT FK_5E8D2F74F8BD700D FOREIGN KEY (unit_id) REFERENCES commodity_unit (id);
ALTER TABLE commodity ADD CONSTRAINT FK_5E8D2F744D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE commodity ADD CONSTRAINT FK_5E8D2F74E6ADA943 FOREIGN KEY (cat_id) REFERENCES commodity_cat (id);
ALTER TABLE pay_info_temp ADD CONSTRAINT FK_7F36E8384D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);
ALTER TABLE pay_info_temp ADD CONSTRAINT FK_7F36E838895648BC FOREIGN KEY (doc_id) REFERENCES hesabdari_doc (id);
ALTER TABLE printer_queue ADD CONSTRAINT FK_93F2764B919E5513 FOREIGN KEY (submitter_id) REFERENCES user (id);
ALTER TABLE printer_queue ADD CONSTRAINT FK_93F2764B4D9866B8 FOREIGN KEY (bid_id) REFERENCES business (id);