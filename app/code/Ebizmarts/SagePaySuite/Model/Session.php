<?php

namespace Ebizmarts\SagePaySuite\Model;

interface Session
{
    const PRESAVED_PENDING_ORDER_KEY = "sagepaysuite_presaved_order_pending_payment";
    const CONVERTING_QUOTE_TO_ORDER = "sagepaysuite_converting_quote_to_order";
    const PARES_SENT = "sagepaysuite_pares_sent";
}
