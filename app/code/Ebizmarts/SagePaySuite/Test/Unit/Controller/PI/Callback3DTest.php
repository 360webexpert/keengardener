<?php
/**
 * Copyright © 2015 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller\PI;

use Ebizmarts\SagePaySuite\Controller\PI\Callback3D;
use Ebizmarts\SagePaySuite\Model\Logger\Logger;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Magento\Sales\Model\Order\Payment;
use Ebizmarts\SagePaySuite\Model\Session as SagePaySession;


class Callback3DTest extends \PHPUnit\Framework\TestCase
{
    private $objectManagerHelper;

    /**
     * Sage Pay Transaction ID
     */
    const TEST_VPSTXID = 'F81FD5E1-12C9-C1D7-5D05-F6E8C12A526F';
    const ORDER_ID = '50';
    const ENCRYPTED_ORDER_ID = '0:3:slozTfXK0r1J23OPKHZkGsqJqT4wudHXPZJXxE9S';
    const ENCODED_ORDER_ID = 'MDozOiswMXF3V0l1WFRLTDRra0wxUCtYSGgyQVdORUdWaXNPN3N5RUNEbzE,';
    const CUSTOMER_ID = '112';
    /**
     * @var /Ebizmarts\SagePaySuite\Controller\PI\Callback3D
     */
    private $piCallback3DController;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var Http|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $redirectMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $urlBuilderMock;

    /**
     * @var Payment
     */
    private $paymentMock;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);

        $this->paymentMock = $this
            ->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    // @codingStandardsIgnoreEnd

    public function testExecuteSUCCESS()
    {
        $pares = "eJzNWdnOo0qSvq+nODp9aZ3DbqDl+iWS3Sxmx\/iOzSxmsQ1me\/rBdlWdf6prZnpao1FbsoDIyMjI2L5I2Dn5PU05O40f9\/Rjp6VdF2bpb0Xy9XcURgiKgilqi1AYiVJbmt7+\/rEzGCvtXgx1l\/25JfAtvP2zK7ImTdbRIb13Rdt8IH\/Cf6I76PvjKvge52HTf+zC+AZk\/QOnMJiAd9C3x12d3mXuY4uhGEHgCPz+7aA3eQf9Nd94PO+6VdmpSD5Q2dzYNhEvNGUClDOQ5dzL8J00PPzrDnpy7JKwTz9QGIVhHKV+Q7Z\/x7G\/Y\/QOetF316c4pm4fq+wt9VzyM2W3muWeNvH8QaHbHfTjaZdO17ZJV451kz\/ud9Bfyl3D5gP+\/KOxVcCTunOOH7u+qD8rRTyVwldZL\/qu68P+0X0EO+jb3S4Oh+GDYRgAuOQoOrWUTOZsevGJF\/ADhsnrZl8suzQuPmBiVWq9vmYxVdbeiz6vn6r+Z8IOeqoCvTz6sbNXH66L3dPfprpquq+\/531\/\/TsEjeP454j92d4zaFUXhmAaWhmS1eV\/+\/09K03k5tz+r6axYdM2RRxWxRL2a4RoaZ+3yW8\/dPuVGMd6SkIgi2f\/WEX9ESN488eTAmMIscqEfi30087+mVV+VvbehX90eYg8F\/hJ0MfOSs\/pMyLS31xL\/vr7336VElyRpV3\/ryz9fdnPEr7L88LqkX6I+HHO5zLB\/HDqCuG23LgtL6BsAbqv3+e9OXfQD12\/beTttU\/WeTOasUiFzTZAM\/hAOCS4ulEgzlgs5WQPTWQdQb5GSd4DuhMOfXSzLylsk67fLuZyqsb4iocezUaK2nPtjVVP\/XagsoWY8qM7GHiq6\/SXh0L7oHzsz6ytJn0p6UJiD9o967QxtSYPJdJHN5SdqnEYGu2j\/os2hMoJ+GdCQLenPafliBbtYVcvVTqFdaVWy42q1iwpXzu05wLly0I4fWbhNTmJt2Lrbwdm7mX0JiGzXR6CUVxQlPUzfMtfKESrweYL6Zfqhc4a4RaEBmgjQJoI4lyhcJn3XS6Qwx2JIGMiOrNgB9bUvkRT6utLdSKMDYNjmeWJ+TKyoTMT6HWyJ4q2FuV4q2GtT\/Zxcom\/qKbKQebXr18+RdE3zyjp\/PbEkYBpLuzD9x2b3vvivIbzWqY0WRZyjmWBOGfMKAMmky1GZk7zaTtzYOOBOfaP7YV0xW7kzGCvtCc5H2KdMXkVmMwYO7yqMReRQVwe5BrredrEOYwKMt0DTOsA4bR3YX5SF6Z\/0zpnX52uMcpntk\/Ap+P+ERyta4QSecQCZ31GQ1+vZF5YYpQuQ1+AQ59+aLY8ykzAeabJ8VPlh0c9l0WPi1CkX+eUJxvsY0xHQp9oZF4HGsCPnMPjGhdMOsej+vMqtCtNHn+ijdmJmriF0d\/6xQ6odOIkElXq8JrGtK+9sUDjnEo\/amY3suZLD5Ef98Bz+IvGym+eXItMtFr1TeYI88Z1D0iMWXPgT9fABmYiXTJHsi6a7Y7y+JKh8tPkxrWXx5g3pzbIo9rMPGmfn0Q3c1G6S962WTTbHOXs+\/57zvas\/SrfcXmBk4VTFddVHfreReb3xPr8tO+YZXyhMbDI2jfRliOMM3nAmC7D4DLgRuY5rjDt6nOTO7QnfWotwY2hGOZvWhWSDbVaS6dyG10GklfkRyEK8bxcr8EZxPflcbw5pyqzZS25nalBlKRpKyUw2tb9Bjr7Tn2ts7DII1hV7xFBiPzkyF1Xr4mX83y77bVZZe3qFKta4Af2MbH6haeF\/EoZZakiZn1Ee1w8IowgRsK5ygyJVg7I9iGLDTHkEcH6\/OWM2ToAy8QS+bJNqaQp2nhZEXOO75srojIWIrLK1mZF18HTvSZ2NIQdqi7nowtR+ApaoGVZqRWCFBN7RaMKA+VgSdU+T4nFMzK8a827VUrYNaDayzVbKjA+pmrGhGlsBqmQetMXOxZs5b0rty1wtLCpaOx+yKi7UomPgck0wDBimWVSveYZwEtQgSy7g4wXgBmvdrdOmhCPezOQlTEAwHQljRFF0c\/hRGK26kwPAaaPavPy5yNA6V5dcyLGmFHNPs9RRLn+MaeKmv0QieMjqasl8PVcrfUhsunqOT+yifJ0lEfpGX8WXAKQjULLuBil3TwbH8zyujX3qN0aiZQmqZHj7cgxL16HMSUIMPLIcCyYsxftDGSJ1WQR0tgi29+YS17s20SyxkNBDZZPzBE6dW\/9hTJY145QeDi5qz4+\/AiwfbfOtVtZNNa\/lrfiPhKtPObaQYW9R1zTXcQS6DPXg2\/7P5QafrDpMiqR\/7EGqKJGr\/WoizCX9p62WMejxlpzhcgTsRqiWuhkQa\/iVb8AdTPzqC8Rql\/f9QjvogXxbU8HBvrksa6nuirXscqtPXTle8u4TNcYM0f+bc8KMOPIfvLNWlNH7m0r420\/k2OylNEA\/KwZCZeZ\/sq4xvRw5bKKqRruMBkpaqOHPvGMInIY4lmXJJvhtZIZNRYXgZ+uy\/BAY1915K+1Mt4X0B9xgCVY\/I6Bb3aPxf76cz1gx3c9YEyWLzEx9Fwz7GoaTkF4k4fxfEnSAQMOLyEtjh+hBzyDw6Et9F4cyMY8JrV0IwFm5oGdeEWgy+KjVedIVDXBkTej1mDWimdkUEk4r2FRtZovtxHblnjGOvKJRZWLCtWZY+vxYYRuqiXVW8vQdWd4cKbM997D4w\/V0tHODXia1o5+GhjH6CHvR9boHCTaQIiTJnYRYeSlnhNb4uJiBSGhOPZnpbi7G87QINmEAElP2lztz+0g6g58FiW5QfKIF42SLyTYVqS109jfam5g6jPabMkRMePSqBjdE8L7QmaiEcaHNn9I43Fo+oj0eOKhWBcU2KfmfDjR8iiyYhp57uDemYc482SBWdfHDvoZcX8JweKyQjBz+AuC9RwOHAAgbuJbdRGP\/ohSFvNLCA6X\/ycItsZR\/AFB+q\/Sz45QGv4GvcgKs\/MKs4hWxqNevaB3ftJeYewEo460\/77tw1qu5fLZElxu+aUQ6RFeU4QXGObAMibFPMfZTFnveeZ+qVkCNjfipTbnE0pflOmKJJtmj2P78ZiLJzIUBMQoNMKuWN42UfTmQWuhwEYpCAyqQEhINM1AB4WAPfD7tnwcZUkYy3FQZkETtpcaBMchNkPKSEhCYJEGO5\/le86XmyqJ6vUouihwbpyD+cifbtxVyi+Pph448rYggPcVntTdwBMs\/txeveJcKYV3ioO7VJDsnkmBHpDJnA21rDWSm0q4vjYXxO0MNTRnmuezNzdEvvcLwkgBRTZO1c\/UkpQF2pdkw6CbADNUxdq72IzIUZzfxKo53qYwVM4XR9W74nSjDWdUwvssbR411h\/PxAOvH9ViiQc\/R92Ne8HVpq81gY0g9ba\/+I3rpZtR5hiTAe1amrqSZZkQ\/wfIEpyC4xaqMzBmkTpEhLJUkw\/ccSTjkc8+Q1Y2rpALoGducf\/H5fQZB+N3KFzllyyAFgZtOTvT2mAMJQv+7+BMRV7QyJn+p1j0VvjGzDc8cnN2uDF4Xp5+wGqyaqLW1SP5R4gv43ocXlDn6uANs8gKd\/oKWfSQsMRzfMyCMMuCw\/pnxXM9XYLja3\/CL9sGp10O3GUIUPw7hFbms43wvfkJmW+502B4rzz9V6EWClBvXvnWFvYJq559Op7WNd+564jVknDM4eU3kwLMmVr7WG3NvWCUzJftDwAEvKBDnFK5DxWWt+mFTS43DrOw5ljiqfJfwx6gg41A030uMYd8OrVm7KynTLZtPLaPaiz3qxUOwL55hGbanvvkoPh9CQcHpyQNN1QO\/nmMo5MtgS1212q18GrydJO9JvGnAQBLRZw4i8Yw4xo1Y+6mpkhNgD5E87wCz1r5mKZZIW1\/TjaxtffarYJZOIhMcRFsKVw71PC+hdrjIutzODHHpBVhnF1QAzbqLYKtBfNaCnNQVDaErxUwRZwLNEid0wijUF3o1UUq5eUg3JY4fA5D4QFtz0VDUkp7vdDbTEoYWUf6iEFQXa\/vZGQiV7eDG0XKF1NsrWP2CBLSDQ50gXa6xyIswWAClbTXsRtQU0E3GHaDiQMK4dx6xMVr4tFtLY\/GuX8O9rj2mdrt7TvsmTweII4FQ2BQwcZ3BeTExME\/lGFXYDhw\/+kk5rkLr2tM9\/0kxnuXyjEdftBY+E2btPpfCM1J4pjwDT3aCmpv6PmcWrLwStdRymNd4+RRL2V0vU4HTkP9J6180WCNYya95Ge\/\/PfT\/Z8\/KbKDRxP+2qXlaM1OFXtWBbqTDJwVHKqI+56DIxgy6qTMUWovH7lbd3AMSUJv6r6E7\/rZk7uTBJgLzldXgew4Y+ws68D7G8RUqMgKPYXEtujdUDIJySD+csoMGc1xCTO8BV+koZeosPWqMfFONx9RbTLIhHq+jihmPcY8gCjktnXZZb7DjkGQ9MlKch5tqZo10BGvjD29xPHGxeD7DaXp+SAvS3XHJoFc7EfZC34XNnQXHItRTCBQt0qr+Q\/uKkMXfNMqnSimlUPhjAM2cD8slGIIbnawx44RK7hyb5SsKtc0dYPiJLYYmHSS7Df62CdQWVeFsFYhcelEaQLDOVSTY7oN9ihPQHj\/\/aRolqunGOp1MuDHJ2SZgraeFdaS94tTBC\/+dIrwDrJmGPMIxZyWLm60AfiY4fqZ65df5NCrleGZIySA83GKWfVyx3XftqBrc1RcZxy1vJ57rXAjw3exPXTzmL42dOzIt5UfWwtrdzBsdXGIA1nsuLalgn6+bM+p6weCjhsaOwiBgZWIvOxV5HIhzo8COkeikvdxZGAqOJsJ611piLgbe3vjuD0o1645prnywu4Xk8IWcnNdlouvdMppD8ET3RxFeZKC5CJ0CTlgGr0h6UDxpzQ\/TbAVmSsGU5FYH9Etsfbk11EdXUp0A762GRQWwSMwQ1K4KFpsxONmw0Wnm8YwEVar+s06boFYwMit4WAaMz0RakKoZVqKYLh6T56hnMXSRyE3VQ6m7az2GE0tjQaV93B7QGSydhBxEK5qdjQCiRu\/fv1FCYT+eg8H\/Xg399dbu9fXiNdnkucL9M+fT\/4DGr5QbA==";

        $this->urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($orderMock);

        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(SagePaySession::PARES_SENT, $pares);

        $this->paymentMock->expects($this->once())
            ->method('save');

        $orderMock
            ->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $postRequest =
            '"PaRes": "eJzNWdnOo0qSvq+nODp9aZ3DbqDl+iWS3Sxmx\/iOzSxmsQ1me\/rBdlWdf6prZnpao1FbsoDIyMjI2L5I2Dn5PU05O40f9\/Rjp6VdF2bpb0Xy9XcURgiKgilqi1AYiVJbmt7+\/rEzGCvtXgx1l\/25JfAtvP2zK7ImTdbRIb13Rdt8IH\/Cf6I76PvjKvge52HTf+zC+AZk\/QOnMJiAd9C3x12d3mXuY4uhGEHgCPz+7aA3eQf9Nd94PO+6VdmpSD5Q2dzYNhEvNGUClDOQ5dzL8J00PPzrDnpy7JKwTz9QGIVhHKV+Q7Z\/x7G\/Y\/QOetF316c4pm4fq+wt9VzyM2W3muWeNvH8QaHbHfTjaZdO17ZJV451kz\/ud9Bfyl3D5gP+\/KOxVcCTunOOH7u+qD8rRTyVwldZL\/qu68P+0X0EO+jb3S4Oh+GDYRgAuOQoOrWUTOZsevGJF\/ADhsnrZl8suzQuPmBiVWq9vmYxVdbeiz6vn6r+Z8IOeqoCvTz6sbNXH66L3dPfprpquq+\/531\/\/TsEjeP454j92d4zaFUXhmAaWhmS1eV\/+\/09K03k5tz+r6axYdM2RRxWxRL2a4RoaZ+3yW8\/dPuVGMd6SkIgi2f\/WEX9ESN488eTAmMIscqEfi30087+mVV+VvbehX90eYg8F\/hJ0MfOSs\/pMyLS31xL\/vr7336VElyRpV3\/ryz9fdnPEr7L88LqkX6I+HHO5zLB\/HDqCuG23LgtL6BsAbqv3+e9OXfQD12\/beTttU\/WeTOasUiFzTZAM\/hAOCS4ulEgzlgs5WQPTWQdQb5GSd4DuhMOfXSzLylsk67fLuZyqsb4iocezUaK2nPtjVVP\/XagsoWY8qM7GHiq6\/SXh0L7oHzsz6ytJn0p6UJiD9o967QxtSYPJdJHN5SdqnEYGu2j\/os2hMoJ+GdCQLenPafliBbtYVcvVTqFdaVWy42q1iwpXzu05wLly0I4fWbhNTmJt2Lrbwdm7mX0JiGzXR6CUVxQlPUzfMtfKESrweYL6Zfqhc4a4RaEBmgjQJoI4lyhcJn3XS6Qwx2JIGMiOrNgB9bUvkRT6utLdSKMDYNjmeWJ+TKyoTMT6HWyJ4q2FuV4q2GtT\/Zxcom\/qKbKQebXr18+RdE3zyjp\/PbEkYBpLuzD9x2b3vvivIbzWqY0WRZyjmWBOGfMKAMmky1GZk7zaTtzYOOBOfaP7YV0xW7kzGCvtCc5H2KdMXkVmMwYO7yqMReRQVwe5BrredrEOYwKMt0DTOsA4bR3YX5SF6Z\/0zpnX52uMcpntk\/Ap+P+ERyta4QSecQCZ31GQ1+vZF5YYpQuQ1+AQ59+aLY8ykzAeabJ8VPlh0c9l0WPi1CkX+eUJxvsY0xHQp9oZF4HGsCPnMPjGhdMOsej+vMqtCtNHn+ijdmJmriF0d\/6xQ6odOIkElXq8JrGtK+9sUDjnEo\/amY3suZLD5Ef98Bz+IvGym+eXItMtFr1TeYI88Z1D0iMWXPgT9fABmYiXTJHsi6a7Y7y+JKh8tPkxrWXx5g3pzbIo9rMPGmfn0Q3c1G6S962WTTbHOXs+\/57zvas\/SrfcXmBk4VTFddVHfreReb3xPr8tO+YZXyhMbDI2jfRliOMM3nAmC7D4DLgRuY5rjDt6nOTO7QnfWotwY2hGOZvWhWSDbVaS6dyG10GklfkRyEK8bxcr8EZxPflcbw5pyqzZS25nalBlKRpKyUw2tb9Bjr7Tn2ts7DII1hV7xFBiPzkyF1Xr4mX83y77bVZZe3qFKta4Af2MbH6haeF\/EoZZakiZn1Ee1w8IowgRsK5ygyJVg7I9iGLDTHkEcH6\/OWM2ToAy8QS+bJNqaQp2nhZEXOO75srojIWIrLK1mZF18HTvSZ2NIQdqi7nowtR+ApaoGVZqRWCFBN7RaMKA+VgSdU+T4nFMzK8a827VUrYNaDayzVbKjA+pmrGhGlsBqmQetMXOxZs5b0rty1wtLCpaOx+yKi7UomPgck0wDBimWVSveYZwEtQgSy7g4wXgBmvdrdOmhCPezOQlTEAwHQljRFF0c\/hRGK26kwPAaaPavPy5yNA6V5dcyLGmFHNPs9RRLn+MaeKmv0QieMjqasl8PVcrfUhsunqOT+yifJ0lEfpGX8WXAKQjULLuBil3TwbH8zyujX3qN0aiZQmqZHj7cgxL16HMSUIMPLIcCyYsxftDGSJ1WQR0tgi29+YS17s20SyxkNBDZZPzBE6dW\/9hTJY145QeDi5qz4+\/AiwfbfOtVtZNNa\/lrfiPhKtPObaQYW9R1zTXcQS6DPXg2\/7P5QafrDpMiqR\/7EGqKJGr\/WoizCX9p62WMejxlpzhcgTsRqiWuhkQa\/iVb8AdTPzqC8Rql\/f9QjvogXxbU8HBvrksa6nuirXscqtPXTle8u4TNcYM0f+bc8KMOPIfvLNWlNH7m0r420\/k2OylNEA\/KwZCZeZ\/sq4xvRw5bKKqRruMBkpaqOHPvGMInIY4lmXJJvhtZIZNRYXgZ+uy\/BAY1915K+1Mt4X0B9xgCVY\/I6Bb3aPxf76cz1gx3c9YEyWLzEx9Fwz7GoaTkF4k4fxfEnSAQMOLyEtjh+hBzyDw6Et9F4cyMY8JrV0IwFm5oGdeEWgy+KjVedIVDXBkTej1mDWimdkUEk4r2FRtZovtxHblnjGOvKJRZWLCtWZY+vxYYRuqiXVW8vQdWd4cKbM997D4w\/V0tHODXia1o5+GhjH6CHvR9boHCTaQIiTJnYRYeSlnhNb4uJiBSGhOPZnpbi7G87QINmEAElP2lztz+0g6g58FiW5QfKIF42SLyTYVqS109jfam5g6jPabMkRMePSqBjdE8L7QmaiEcaHNn9I43Fo+oj0eOKhWBcU2KfmfDjR8iiyYhp57uDemYc482SBWdfHDvoZcX8JweKyQjBz+AuC9RwOHAAgbuJbdRGP\/ohSFvNLCA6X\/ycItsZR\/AFB+q\/Sz45QGv4GvcgKs\/MKs4hWxqNevaB3ftJeYewEo460\/77tw1qu5fLZElxu+aUQ6RFeU4QXGObAMibFPMfZTFnveeZ+qVkCNjfipTbnE0pflOmKJJtmj2P78ZiLJzIUBMQoNMKuWN42UfTmQWuhwEYpCAyqQEhINM1AB4WAPfD7tnwcZUkYy3FQZkETtpcaBMchNkPKSEhCYJEGO5\/le86XmyqJ6vUouihwbpyD+cifbtxVyi+Pph448rYggPcVntTdwBMs\/txeveJcKYV3ioO7VJDsnkmBHpDJnA21rDWSm0q4vjYXxO0MNTRnmuezNzdEvvcLwkgBRTZO1c\/UkpQF2pdkw6CbADNUxdq72IzIUZzfxKo53qYwVM4XR9W74nSjDWdUwvssbR411h\/PxAOvH9ViiQc\/R92Ne8HVpq81gY0g9ba\/+I3rpZtR5hiTAe1amrqSZZkQ\/wfIEpyC4xaqMzBmkTpEhLJUkw\/ccSTjkc8+Q1Y2rpALoGducf\/H5fQZB+N3KFzllyyAFgZtOTvT2mAMJQv+7+BMRV7QyJn+p1j0VvjGzDc8cnN2uDF4Xp5+wGqyaqLW1SP5R4gv43ocXlDn6uANs8gKd\/oKWfSQsMRzfMyCMMuCw\/pnxXM9XYLja3\/CL9sGp10O3GUIUPw7hFbms43wvfkJmW+502B4rzz9V6EWClBvXvnWFvYJq559Op7WNd+564jVknDM4eU3kwLMmVr7WG3NvWCUzJftDwAEvKBDnFK5DxWWt+mFTS43DrOw5ljiqfJfwx6gg41A030uMYd8OrVm7KynTLZtPLaPaiz3qxUOwL55hGbanvvkoPh9CQcHpyQNN1QO\/nmMo5MtgS1212q18GrydJO9JvGnAQBLRZw4i8Yw4xo1Y+6mpkhNgD5E87wCz1r5mKZZIW1\/TjaxtffarYJZOIhMcRFsKVw71PC+hdrjIutzODHHpBVhnF1QAzbqLYKtBfNaCnNQVDaErxUwRZwLNEid0wijUF3o1UUq5eUg3JY4fA5D4QFtz0VDUkp7vdDbTEoYWUf6iEFQXa\/vZGQiV7eDG0XKF1NsrWP2CBLSDQ50gXa6xyIswWAClbTXsRtQU0E3GHaDiQMK4dx6xMVr4tFtLY\/GuX8O9rj2mdrt7TvsmTweII4FQ2BQwcZ3BeTExME\/lGFXYDhw\/+kk5rkLr2tM9\/0kxnuXyjEdftBY+E2btPpfCM1J4pjwDT3aCmpv6PmcWrLwStdRymNd4+RRL2V0vU4HTkP9J6180WCNYya95Ge\/\/PfT\/Z8\/KbKDRxP+2qXlaM1OFXtWBbqTDJwVHKqI+56DIxgy6qTMUWovH7lbd3AMSUJv6r6E7\/rZk7uTBJgLzldXgew4Y+ws68D7G8RUqMgKPYXEtujdUDIJySD+csoMGc1xCTO8BV+koZeosPWqMfFONx9RbTLIhHq+jihmPcY8gCjktnXZZb7DjkGQ9MlKch5tqZo10BGvjD29xPHGxeD7DaXp+SAvS3XHJoFc7EfZC34XNnQXHItRTCBQt0qr+Q\/uKkMXfNMqnSimlUPhjAM2cD8slGIIbnawx44RK7hyb5SsKtc0dYPiJLYYmHSS7Df62CdQWVeFsFYhcelEaQLDOVSTY7oN9ihPQHj\/\/aRolqunGOp1MuDHJ2SZgraeFdaS94tTBC\/+dIrwDrJmGPMIxZyWLm60AfiY4fqZ65df5NCrleGZIySA83GKWfVyx3XftqBrc1RcZxy1vJ57rXAjw3exPXTzmL42dOzIt5UfWwtrdzBsdXGIA1nsuLalgn6+bM+p6weCjhsaOwiBgZWIvOxV5HIhzo8COkeikvdxZGAqOJsJ611piLgbe3vjuD0o1645prnywu4Xk8IWcnNdlouvdMppD8ET3RxFeZKC5CJ0CTlgGr0h6UDxpzQ\/TbAVmSsGU5FYH9Etsfbk11EdXUp0A762GRQWwSMwQ1K4KFpsxONmw0Wnm8YwEVar+s06boFYwMit4WAaMz0RakKoZVqKYLh6T56hnMXSRyE3VQ6m7az2GE0tjQaV93B7QGSydhBxEK5qdjQCiRu\/fv1FCYT+eg8H\/Xg399dbu9fXiNdnkucL9M+fT\/4DGr5QbA==",
            "PaReq": "eJxVUk1PwzAMvfMrqh24IC1p+rF0eEEdE1BgqLAJBLeQBjaJpiNt6bZfT9J1bOTkZzvP9rPhYp1\/OT9Sl8tCjXpuH\/cu2AnMF1rKyUyKWksGU1mW\/FM6y2zUI9gNKMWUhi71BoSGURT2GKTxk\/xm0BExw9MngPbQMGix4KpiwMX3OHlgPvVwgAF1EHKpkwkLPeIFge\/i3QO0c4PiuWSXhVZy45zyfHXujLnWRePczyeA2iiIolaV3jBKQkB7ALX+YouqWg0RapqmL1oOrrL39r\/BOSCbA+jQYlpbqzSc62XGSPJ4NpsFYhvRxzGZpO72o0qwHqTP\/giQzYCMV5IRTDD2CXXccOh7Qy8C1PqB57YZFlI7UAdgZWvEx5FjDxjdtVRiP80egVyvCiVNhhH3z4ZMloLN7IZSvnGmaWJKWxegwyiXN1Z7URk5iUq5P305m7\/dhYvaG19lMblu4tu4eR3ZjbRJttTSKEgIjtpaFgCyNKhbNuruwlj\/7uUXPenA8Q==",
            "MD": "",
            "DeviceID": "crMgt2ljUKvH3u++TgS2u8JI7ju4nLnfWOUd5CBamwF8\/FVUjS7AQq9d8ulTPKNM",
            "ABSlog": "GPP",
            "deviceDNA": "",
            "executionTime": "",
            "dnaError": "",
            "mesc": "",
            "mescIterationCount": "",
            "desc": "",
            "isDNADone": "false",
            "arcotFlashCookie": ""';

        $this->makeRequestMock($pares, $postRequest);

        $suiteLoggerMock = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutSessionMock = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutSessionMock
            ->expects($this->once())
            ->method('setData')
            ->with(\Ebizmarts\SagePaySuite\Model\Session::PARES_SENT, $pares)
            ->willReturnSelf();

        $cryptAndCodeMock = $this
            ->getMockBuilder(CryptAndCodeData::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cryptAndCodeMock
            ->expects($this->once())
            ->method('decodeAndDecrypt')
            ->with(self::ENCODED_ORDER_ID)
            ->willReturn(self::ORDER_ID);

        $this->redirectMock = $this
            ->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $messageManagerMock = $this
            ->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->makeContextMock($messageManagerMock);

        $configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $piRequestManagerMock = $this->makeRequestManagerMock($pares);

        $piRequestManagerDataFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $piRequestManagerDataFactoryMock->expects($this->once())->method('create')->willReturn($piRequestManagerMock);

        $resultMock = $this->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiResult')
            ->disableOriginalConstructor()->getMock();
        $resultMock->expects($this->once())->method('getErrorMessage')->willReturnArgument(null);

        $threeDCallbackManagementMock = $this->makeThreeDCallbackManagementMock($resultMock);

        $orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::CUSTOMER_ID);
        $customerRepositoryMock = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerInterfaceMock = $this
            ->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($customerInterfaceMock);
        $customerSessionMock = $this
            ->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock
            ->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerInterfaceMock)
            ->willReturnSelf();

        $this->piCallback3DController = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\PI\Callback3D',
            [
                'context'                     => $contextMock,
                'config'                      => $configMock,
                'piRequestManagerDataFactory' => $piRequestManagerDataFactoryMock,
                'requester'                   => $threeDCallbackManagementMock,
                'orderRepository'             => $orderRepositoryMock,
                'cryptAndCode'                => $cryptAndCodeMock,
                'checkoutSession'             => $checkoutSessionMock,
                'suiteLogger'                 => $suiteLoggerMock,
                'customerSession'             => $customerSessionMock,
                'customerRepository'          => $customerRepositoryMock
            ]
        );

        $this->expectSetBody(
            '<script>window.top.location.href = "'
            . $this->urlBuilderMock->getUrl('checkout/onepage/success', ['_secure' => true])
            . '";</script>'
        );

        $this->piCallback3DController->execute();
    }

    public function testExecuteSUCCESSParesWithSpaces()
    {
        $pares = "123    456   7
        8
        0";
        $sanitizedPares = "123456780";

        $this->urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($orderMock);

        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(SagePaySession::PARES_SENT, $sanitizedPares);

        $this->paymentMock->expects($this->once())
            ->method('save');

        $orderMock
            ->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $postRequest =
            '"PaRes": "eJzNWdnOo0qSvq+nODp9aZ3DbqDl+iWS3Sxmx\/iOzSxmsQ1me\/rBdlWdf6prZnpao1FbsoDIyMjI2L5I2Dn5PU05O40f9\/Rjp6VdF2bpb0Xy9XcURgiKgilqi1AYiVJbmt7+\/rEzGCvtXgx1l\/25JfAtvP2zK7ImTdbRIb13Rdt8IH\/Cf6I76PvjKvge52HTf+zC+AZk\/QOnMJiAd9C3x12d3mXuY4uhGEHgCPz+7aA3eQf9Nd94PO+6VdmpSD5Q2dzYNhEvNGUClDOQ5dzL8J00PPzrDnpy7JKwTz9QGIVhHKV+Q7Z\/x7G\/Y\/QOetF316c4pm4fq+wt9VzyM2W3muWeNvH8QaHbHfTjaZdO17ZJV451kz\/ud9Bfyl3D5gP+\/KOxVcCTunOOH7u+qD8rRTyVwldZL\/qu68P+0X0EO+jb3S4Oh+GDYRgAuOQoOrWUTOZsevGJF\/ADhsnrZl8suzQuPmBiVWq9vmYxVdbeiz6vn6r+Z8IOeqoCvTz6sbNXH66L3dPfprpquq+\/531\/\/TsEjeP454j92d4zaFUXhmAaWhmS1eV\/+\/09K03k5tz+r6axYdM2RRxWxRL2a4RoaZ+3yW8\/dPuVGMd6SkIgi2f\/WEX9ESN488eTAmMIscqEfi30087+mVV+VvbehX90eYg8F\/hJ0MfOSs\/pMyLS31xL\/vr7336VElyRpV3\/ryz9fdnPEr7L88LqkX6I+HHO5zLB\/HDqCuG23LgtL6BsAbqv3+e9OXfQD12\/beTttU\/WeTOasUiFzTZAM\/hAOCS4ulEgzlgs5WQPTWQdQb5GSd4DuhMOfXSzLylsk67fLuZyqsb4iocezUaK2nPtjVVP\/XagsoWY8qM7GHiq6\/SXh0L7oHzsz6ytJn0p6UJiD9o967QxtSYPJdJHN5SdqnEYGu2j\/os2hMoJ+GdCQLenPafliBbtYVcvVTqFdaVWy42q1iwpXzu05wLly0I4fWbhNTmJt2Lrbwdm7mX0JiGzXR6CUVxQlPUzfMtfKESrweYL6Zfqhc4a4RaEBmgjQJoI4lyhcJn3XS6Qwx2JIGMiOrNgB9bUvkRT6utLdSKMDYNjmeWJ+TKyoTMT6HWyJ4q2FuV4q2GtT\/Zxcom\/qKbKQebXr18+RdE3zyjp\/PbEkYBpLuzD9x2b3vvivIbzWqY0WRZyjmWBOGfMKAMmky1GZk7zaTtzYOOBOfaP7YV0xW7kzGCvtCc5H2KdMXkVmMwYO7yqMReRQVwe5BrredrEOYwKMt0DTOsA4bR3YX5SF6Z\/0zpnX52uMcpntk\/Ap+P+ERyta4QSecQCZ31GQ1+vZF5YYpQuQ1+AQ59+aLY8ykzAeabJ8VPlh0c9l0WPi1CkX+eUJxvsY0xHQp9oZF4HGsCPnMPjGhdMOsej+vMqtCtNHn+ijdmJmriF0d\/6xQ6odOIkElXq8JrGtK+9sUDjnEo\/amY3suZLD5Ef98Bz+IvGym+eXItMtFr1TeYI88Z1D0iMWXPgT9fABmYiXTJHsi6a7Y7y+JKh8tPkxrWXx5g3pzbIo9rMPGmfn0Q3c1G6S962WTTbHOXs+\/57zvas\/SrfcXmBk4VTFddVHfreReb3xPr8tO+YZXyhMbDI2jfRliOMM3nAmC7D4DLgRuY5rjDt6nOTO7QnfWotwY2hGOZvWhWSDbVaS6dyG10GklfkRyEK8bxcr8EZxPflcbw5pyqzZS25nalBlKRpKyUw2tb9Bjr7Tn2ts7DII1hV7xFBiPzkyF1Xr4mX83y77bVZZe3qFKta4Af2MbH6haeF\/EoZZakiZn1Ee1w8IowgRsK5ygyJVg7I9iGLDTHkEcH6\/OWM2ToAy8QS+bJNqaQp2nhZEXOO75srojIWIrLK1mZF18HTvSZ2NIQdqi7nowtR+ApaoGVZqRWCFBN7RaMKA+VgSdU+T4nFMzK8a827VUrYNaDayzVbKjA+pmrGhGlsBqmQetMXOxZs5b0rty1wtLCpaOx+yKi7UomPgck0wDBimWVSveYZwEtQgSy7g4wXgBmvdrdOmhCPezOQlTEAwHQljRFF0c\/hRGK26kwPAaaPavPy5yNA6V5dcyLGmFHNPs9RRLn+MaeKmv0QieMjqasl8PVcrfUhsunqOT+yifJ0lEfpGX8WXAKQjULLuBil3TwbH8zyujX3qN0aiZQmqZHj7cgxL16HMSUIMPLIcCyYsxftDGSJ1WQR0tgi29+YS17s20SyxkNBDZZPzBE6dW\/9hTJY145QeDi5qz4+\/AiwfbfOtVtZNNa\/lrfiPhKtPObaQYW9R1zTXcQS6DPXg2\/7P5QafrDpMiqR\/7EGqKJGr\/WoizCX9p62WMejxlpzhcgTsRqiWuhkQa\/iVb8AdTPzqC8Rql\/f9QjvogXxbU8HBvrksa6nuirXscqtPXTle8u4TNcYM0f+bc8KMOPIfvLNWlNH7m0r420\/k2OylNEA\/KwZCZeZ\/sq4xvRw5bKKqRruMBkpaqOHPvGMInIY4lmXJJvhtZIZNRYXgZ+uy\/BAY1915K+1Mt4X0B9xgCVY\/I6Bb3aPxf76cz1gx3c9YEyWLzEx9Fwz7GoaTkF4k4fxfEnSAQMOLyEtjh+hBzyDw6Et9F4cyMY8JrV0IwFm5oGdeEWgy+KjVedIVDXBkTej1mDWimdkUEk4r2FRtZovtxHblnjGOvKJRZWLCtWZY+vxYYRuqiXVW8vQdWd4cKbM997D4w\/V0tHODXia1o5+GhjH6CHvR9boHCTaQIiTJnYRYeSlnhNb4uJiBSGhOPZnpbi7G87QINmEAElP2lztz+0g6g58FiW5QfKIF42SLyTYVqS109jfam5g6jPabMkRMePSqBjdE8L7QmaiEcaHNn9I43Fo+oj0eOKhWBcU2KfmfDjR8iiyYhp57uDemYc482SBWdfHDvoZcX8JweKyQjBz+AuC9RwOHAAgbuJbdRGP\/ohSFvNLCA6X\/ycItsZR\/AFB+q\/Sz45QGv4GvcgKs\/MKs4hWxqNevaB3ftJeYewEo460\/77tw1qu5fLZElxu+aUQ6RFeU4QXGObAMibFPMfZTFnveeZ+qVkCNjfipTbnE0pflOmKJJtmj2P78ZiLJzIUBMQoNMKuWN42UfTmQWuhwEYpCAyqQEhINM1AB4WAPfD7tnwcZUkYy3FQZkETtpcaBMchNkPKSEhCYJEGO5\/le86XmyqJ6vUouihwbpyD+cifbtxVyi+Pph448rYggPcVntTdwBMs\/txeveJcKYV3ioO7VJDsnkmBHpDJnA21rDWSm0q4vjYXxO0MNTRnmuezNzdEvvcLwkgBRTZO1c\/UkpQF2pdkw6CbADNUxdq72IzIUZzfxKo53qYwVM4XR9W74nSjDWdUwvssbR411h\/PxAOvH9ViiQc\/R92Ne8HVpq81gY0g9ba\/+I3rpZtR5hiTAe1amrqSZZkQ\/wfIEpyC4xaqMzBmkTpEhLJUkw\/ccSTjkc8+Q1Y2rpALoGducf\/H5fQZB+N3KFzllyyAFgZtOTvT2mAMJQv+7+BMRV7QyJn+p1j0VvjGzDc8cnN2uDF4Xp5+wGqyaqLW1SP5R4gv43ocXlDn6uANs8gKd\/oKWfSQsMRzfMyCMMuCw\/pnxXM9XYLja3\/CL9sGp10O3GUIUPw7hFbms43wvfkJmW+502B4rzz9V6EWClBvXvnWFvYJq559Op7WNd+564jVknDM4eU3kwLMmVr7WG3NvWCUzJftDwAEvKBDnFK5DxWWt+mFTS43DrOw5ljiqfJfwx6gg41A030uMYd8OrVm7KynTLZtPLaPaiz3qxUOwL55hGbanvvkoPh9CQcHpyQNN1QO\/nmMo5MtgS1212q18GrydJO9JvGnAQBLRZw4i8Yw4xo1Y+6mpkhNgD5E87wCz1r5mKZZIW1\/TjaxtffarYJZOIhMcRFsKVw71PC+hdrjIutzODHHpBVhnF1QAzbqLYKtBfNaCnNQVDaErxUwRZwLNEid0wijUF3o1UUq5eUg3JY4fA5D4QFtz0VDUkp7vdDbTEoYWUf6iEFQXa\/vZGQiV7eDG0XKF1NsrWP2CBLSDQ50gXa6xyIswWAClbTXsRtQU0E3GHaDiQMK4dx6xMVr4tFtLY\/GuX8O9rj2mdrt7TvsmTweII4FQ2BQwcZ3BeTExME\/lGFXYDhw\/+kk5rkLr2tM9\/0kxnuXyjEdftBY+E2btPpfCM1J4pjwDT3aCmpv6PmcWrLwStdRymNd4+RRL2V0vU4HTkP9J6180WCNYya95Ge\/\/PfT\/Z8\/KbKDRxP+2qXlaM1OFXtWBbqTDJwVHKqI+56DIxgy6qTMUWovH7lbd3AMSUJv6r6E7\/rZk7uTBJgLzldXgew4Y+ws68D7G8RUqMgKPYXEtujdUDIJySD+csoMGc1xCTO8BV+koZeosPWqMfFONx9RbTLIhHq+jihmPcY8gCjktnXZZb7DjkGQ9MlKch5tqZo10BGvjD29xPHGxeD7DaXp+SAvS3XHJoFc7EfZC34XNnQXHItRTCBQt0qr+Q\/uKkMXfNMqnSimlUPhjAM2cD8slGIIbnawx44RK7hyb5SsKtc0dYPiJLYYmHSS7Df62CdQWVeFsFYhcelEaQLDOVSTY7oN9ihPQHj\/\/aRolqunGOp1MuDHJ2SZgraeFdaS94tTBC\/+dIrwDrJmGPMIxZyWLm60AfiY4fqZ65df5NCrleGZIySA83GKWfVyx3XftqBrc1RcZxy1vJ57rXAjw3exPXTzmL42dOzIt5UfWwtrdzBsdXGIA1nsuLalgn6+bM+p6weCjhsaOwiBgZWIvOxV5HIhzo8COkeikvdxZGAqOJsJ611piLgbe3vjuD0o1645prnywu4Xk8IWcnNdlouvdMppD8ET3RxFeZKC5CJ0CTlgGr0h6UDxpzQ\/TbAVmSsGU5FYH9Etsfbk11EdXUp0A762GRQWwSMwQ1K4KFpsxONmw0Wnm8YwEVar+s06boFYwMit4WAaMz0RakKoZVqKYLh6T56hnMXSRyE3VQ6m7az2GE0tjQaV93B7QGSydhBxEK5qdjQCiRu\/fv1FCYT+eg8H\/Xg399dbu9fXiNdnkucL9M+fT\/4DGr5QbA==",
            "PaReq": "eJxVUk1PwzAMvfMrqh24IC1p+rF0eEEdE1BgqLAJBLeQBjaJpiNt6bZfT9J1bOTkZzvP9rPhYp1\/OT9Sl8tCjXpuH\/cu2AnMF1rKyUyKWksGU1mW\/FM6y2zUI9gNKMWUhi71BoSGURT2GKTxk\/xm0BExw9MngPbQMGix4KpiwMX3OHlgPvVwgAF1EHKpkwkLPeIFge\/i3QO0c4PiuWSXhVZy45zyfHXujLnWRePczyeA2iiIolaV3jBKQkB7ALX+YouqWg0RapqmL1oOrrL39r\/BOSCbA+jQYlpbqzSc62XGSPJ4NpsFYhvRxzGZpO72o0qwHqTP\/giQzYCMV5IRTDD2CXXccOh7Qy8C1PqB57YZFlI7UAdgZWvEx5FjDxjdtVRiP80egVyvCiVNhhH3z4ZMloLN7IZSvnGmaWJKWxegwyiXN1Z7URk5iUq5P305m7\/dhYvaG19lMblu4tu4eR3ZjbRJttTSKEgIjtpaFgCyNKhbNuruwlj\/7uUXPenA8Q==",
            "MD": "",
            "DeviceID": "crMgt2ljUKvH3u++TgS2u8JI7ju4nLnfWOUd5CBamwF8\/FVUjS7AQq9d8ulTPKNM",
            "ABSlog": "GPP",
            "deviceDNA": "",
            "executionTime": "",
            "dnaError": "",
            "mesc": "",
            "mescIterationCount": "",
            "desc": "",
            "isDNADone": "false",
            "arcotFlashCookie": ""';

        $this->makeRequestMock($pares, $postRequest);

        $suiteLoggerMock = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutSessionMock = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(['setData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $cryptAndCodeMock = $this
            ->getMockBuilder(CryptAndCodeData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cryptAndCodeMock
            ->expects($this->once())
            ->method('decodeAndDecrypt')
            ->with(self::ENCODED_ORDER_ID)
            ->willReturn(self::ORDER_ID);

        $this->redirectMock = $this
            ->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $messageManagerMock = $this
            ->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->makeContextMock($messageManagerMock);

        $configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $piRequestManagerMock = $this->makeRequestManagerMock($sanitizedPares);

        $piRequestManagerDataFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $piRequestManagerDataFactoryMock->expects($this->once())->method('create')->willReturn($piRequestManagerMock);

        $resultMock = $this->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiResult')
            ->disableOriginalConstructor()->getMock();
        $resultMock->expects($this->once())->method('getErrorMessage')->willReturnArgument(null);

        $threeDCallbackManagementMock = $this->makeThreeDCallbackManagementMock($resultMock);

        $orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::CUSTOMER_ID);
        $customerRepositoryMock = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerInterfaceMock = $this
            ->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($customerInterfaceMock);
        $customerSessionMock = $this
            ->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock
            ->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerInterfaceMock)
            ->willReturnSelf();

        $this->piCallback3DController = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\PI\Callback3D',
            [
                'context'                     => $contextMock,
                'config'                      => $configMock,
                'piRequestManagerDataFactory' => $piRequestManagerDataFactoryMock,
                'requester'                   => $threeDCallbackManagementMock,
                'orderRepository'             => $orderRepositoryMock,
                'cryptAndCode'                => $cryptAndCodeMock,
                'checkoutSession'             => $checkoutSessionMock,
                'suiteLogger'                 => $suiteLoggerMock,
                'customerSession'             => $customerSessionMock,
                'customerRepository'          => $customerRepositoryMock
            ]
        );

        $this->expectSetBody(
            '<script>window.top.location.href = "'
            . $this->urlBuilderMock->getUrl('checkout/onepage/success', ['_secure' => true])
            . '";</script>'
        );

        $this->piCallback3DController->execute();
    }

    public function testExecuteOrderStateNotPendingPayment()
    {
        $pares = "eJzNWdnOo0qSvq+nODp9aZ3DbqDl+iWS3Sxmx\/iOzSxmsQ1me\/rBdlWdf6prZnpao1FbsoDIyMjI2L5I2Dn5PU05O40f9\/Rjp6VdF2bpb0Xy9XcURgiKgilqi1AYiVJbmt7+\/rEzGCvtXgx1l\/25JfAtvP2zK7ImTdbRIb13Rdt8IH\/Cf6I76PvjKvge52HTf+zC+AZk\/QOnMJiAd9C3x12d3mXuY4uhGEHgCPz+7aA3eQf9Nd94PO+6VdmpSD5Q2dzYNhEvNGUClDOQ5dzL8J00PPzrDnpy7JKwTz9QGIVhHKV+Q7Z\/x7G\/Y\/QOetF316c4pm4fq+wt9VzyM2W3muWeNvH8QaHbHfTjaZdO17ZJV451kz\/ud9Bfyl3D5gP+\/KOxVcCTunOOH7u+qD8rRTyVwldZL\/qu68P+0X0EO+jb3S4Oh+GDYRgAuOQoOrWUTOZsevGJF\/ADhsnrZl8suzQuPmBiVWq9vmYxVdbeiz6vn6r+Z8IOeqoCvTz6sbNXH66L3dPfprpquq+\/531\/\/TsEjeP454j92d4zaFUXhmAaWhmS1eV\/+\/09K03k5tz+r6axYdM2RRxWxRL2a4RoaZ+3yW8\/dPuVGMd6SkIgi2f\/WEX9ESN488eTAmMIscqEfi30087+mVV+VvbehX90eYg8F\/hJ0MfOSs\/pMyLS31xL\/vr7336VElyRpV3\/ryz9fdnPEr7L88LqkX6I+HHO5zLB\/HDqCuG23LgtL6BsAbqv3+e9OXfQD12\/beTttU\/WeTOasUiFzTZAM\/hAOCS4ulEgzlgs5WQPTWQdQb5GSd4DuhMOfXSzLylsk67fLuZyqsb4iocezUaK2nPtjVVP\/XagsoWY8qM7GHiq6\/SXh0L7oHzsz6ytJn0p6UJiD9o967QxtSYPJdJHN5SdqnEYGu2j\/os2hMoJ+GdCQLenPafliBbtYVcvVTqFdaVWy42q1iwpXzu05wLly0I4fWbhNTmJt2Lrbwdm7mX0JiGzXR6CUVxQlPUzfMtfKESrweYL6Zfqhc4a4RaEBmgjQJoI4lyhcJn3XS6Qwx2JIGMiOrNgB9bUvkRT6utLdSKMDYNjmeWJ+TKyoTMT6HWyJ4q2FuV4q2GtT\/Zxcom\/qKbKQebXr18+RdE3zyjp\/PbEkYBpLuzD9x2b3vvivIbzWqY0WRZyjmWBOGfMKAMmky1GZk7zaTtzYOOBOfaP7YV0xW7kzGCvtCc5H2KdMXkVmMwYO7yqMReRQVwe5BrredrEOYwKMt0DTOsA4bR3YX5SF6Z\/0zpnX52uMcpntk\/Ap+P+ERyta4QSecQCZ31GQ1+vZF5YYpQuQ1+AQ59+aLY8ykzAeabJ8VPlh0c9l0WPi1CkX+eUJxvsY0xHQp9oZF4HGsCPnMPjGhdMOsej+vMqtCtNHn+ijdmJmriF0d\/6xQ6odOIkElXq8JrGtK+9sUDjnEo\/amY3suZLD5Ef98Bz+IvGym+eXItMtFr1TeYI88Z1D0iMWXPgT9fABmYiXTJHsi6a7Y7y+JKh8tPkxrWXx5g3pzbIo9rMPGmfn0Q3c1G6S962WTTbHOXs+\/57zvas\/SrfcXmBk4VTFddVHfreReb3xPr8tO+YZXyhMbDI2jfRliOMM3nAmC7D4DLgRuY5rjDt6nOTO7QnfWotwY2hGOZvWhWSDbVaS6dyG10GklfkRyEK8bxcr8EZxPflcbw5pyqzZS25nalBlKRpKyUw2tb9Bjr7Tn2ts7DII1hV7xFBiPzkyF1Xr4mX83y77bVZZe3qFKta4Af2MbH6haeF\/EoZZakiZn1Ee1w8IowgRsK5ygyJVg7I9iGLDTHkEcH6\/OWM2ToAy8QS+bJNqaQp2nhZEXOO75srojIWIrLK1mZF18HTvSZ2NIQdqi7nowtR+ApaoGVZqRWCFBN7RaMKA+VgSdU+T4nFMzK8a827VUrYNaDayzVbKjA+pmrGhGlsBqmQetMXOxZs5b0rty1wtLCpaOx+yKi7UomPgck0wDBimWVSveYZwEtQgSy7g4wXgBmvdrdOmhCPezOQlTEAwHQljRFF0c\/hRGK26kwPAaaPavPy5yNA6V5dcyLGmFHNPs9RRLn+MaeKmv0QieMjqasl8PVcrfUhsunqOT+yifJ0lEfpGX8WXAKQjULLuBil3TwbH8zyujX3qN0aiZQmqZHj7cgxL16HMSUIMPLIcCyYsxftDGSJ1WQR0tgi29+YS17s20SyxkNBDZZPzBE6dW\/9hTJY145QeDi5qz4+\/AiwfbfOtVtZNNa\/lrfiPhKtPObaQYW9R1zTXcQS6DPXg2\/7P5QafrDpMiqR\/7EGqKJGr\/WoizCX9p62WMejxlpzhcgTsRqiWuhkQa\/iVb8AdTPzqC8Rql\/f9QjvogXxbU8HBvrksa6nuirXscqtPXTle8u4TNcYM0f+bc8KMOPIfvLNWlNH7m0r420\/k2OylNEA\/KwZCZeZ\/sq4xvRw5bKKqRruMBkpaqOHPvGMInIY4lmXJJvhtZIZNRYXgZ+uy\/BAY1915K+1Mt4X0B9xgCVY\/I6Bb3aPxf76cz1gx3c9YEyWLzEx9Fwz7GoaTkF4k4fxfEnSAQMOLyEtjh+hBzyDw6Et9F4cyMY8JrV0IwFm5oGdeEWgy+KjVedIVDXBkTej1mDWimdkUEk4r2FRtZovtxHblnjGOvKJRZWLCtWZY+vxYYRuqiXVW8vQdWd4cKbM997D4w\/V0tHODXia1o5+GhjH6CHvR9boHCTaQIiTJnYRYeSlnhNb4uJiBSGhOPZnpbi7G87QINmEAElP2lztz+0g6g58FiW5QfKIF42SLyTYVqS109jfam5g6jPabMkRMePSqBjdE8L7QmaiEcaHNn9I43Fo+oj0eOKhWBcU2KfmfDjR8iiyYhp57uDemYc482SBWdfHDvoZcX8JweKyQjBz+AuC9RwOHAAgbuJbdRGP\/ohSFvNLCA6X\/ycItsZR\/AFB+q\/Sz45QGv4GvcgKs\/MKs4hWxqNevaB3ftJeYewEo460\/77tw1qu5fLZElxu+aUQ6RFeU4QXGObAMibFPMfZTFnveeZ+qVkCNjfipTbnE0pflOmKJJtmj2P78ZiLJzIUBMQoNMKuWN42UfTmQWuhwEYpCAyqQEhINM1AB4WAPfD7tnwcZUkYy3FQZkETtpcaBMchNkPKSEhCYJEGO5\/le86XmyqJ6vUouihwbpyD+cifbtxVyi+Pph448rYggPcVntTdwBMs\/txeveJcKYV3ioO7VJDsnkmBHpDJnA21rDWSm0q4vjYXxO0MNTRnmuezNzdEvvcLwkgBRTZO1c\/UkpQF2pdkw6CbADNUxdq72IzIUZzfxKo53qYwVM4XR9W74nSjDWdUwvssbR411h\/PxAOvH9ViiQc\/R92Ne8HVpq81gY0g9ba\/+I3rpZtR5hiTAe1amrqSZZkQ\/wfIEpyC4xaqMzBmkTpEhLJUkw\/ccSTjkc8+Q1Y2rpALoGducf\/H5fQZB+N3KFzllyyAFgZtOTvT2mAMJQv+7+BMRV7QyJn+p1j0VvjGzDc8cnN2uDF4Xp5+wGqyaqLW1SP5R4gv43ocXlDn6uANs8gKd\/oKWfSQsMRzfMyCMMuCw\/pnxXM9XYLja3\/CL9sGp10O3GUIUPw7hFbms43wvfkJmW+502B4rzz9V6EWClBvXvnWFvYJq559Op7WNd+564jVknDM4eU3kwLMmVr7WG3NvWCUzJftDwAEvKBDnFK5DxWWt+mFTS43DrOw5ljiqfJfwx6gg41A030uMYd8OrVm7KynTLZtPLaPaiz3qxUOwL55hGbanvvkoPh9CQcHpyQNN1QO\/nmMo5MtgS1212q18GrydJO9JvGnAQBLRZw4i8Yw4xo1Y+6mpkhNgD5E87wCz1r5mKZZIW1\/TjaxtffarYJZOIhMcRFsKVw71PC+hdrjIutzODHHpBVhnF1QAzbqLYKtBfNaCnNQVDaErxUwRZwLNEid0wijUF3o1UUq5eUg3JY4fA5D4QFtz0VDUkp7vdDbTEoYWUf6iEFQXa\/vZGQiV7eDG0XKF1NsrWP2CBLSDQ50gXa6xyIswWAClbTXsRtQU0E3GHaDiQMK4dx6xMVr4tFtLY\/GuX8O9rj2mdrt7TvsmTweII4FQ2BQwcZ3BeTExME\/lGFXYDhw\/+kk5rkLr2tM9\/0kxnuXyjEdftBY+E2btPpfCM1J4pjwDT3aCmpv6PmcWrLwStdRymNd4+RRL2V0vU4HTkP9J6180WCNYya95Ge\/\/PfT\/Z8\/KbKDRxP+2qXlaM1OFXtWBbqTDJwVHKqI+56DIxgy6qTMUWovH7lbd3AMSUJv6r6E7\/rZk7uTBJgLzldXgew4Y+ws68D7G8RUqMgKPYXEtujdUDIJySD+csoMGc1xCTO8BV+koZeosPWqMfFONx9RbTLIhHq+jihmPcY8gCjktnXZZb7DjkGQ9MlKch5tqZo10BGvjD29xPHGxeD7DaXp+SAvS3XHJoFc7EfZC34XNnQXHItRTCBQt0qr+Q\/uKkMXfNMqnSimlUPhjAM2cD8slGIIbnawx44RK7hyb5SsKtc0dYPiJLYYmHSS7Df62CdQWVeFsFYhcelEaQLDOVSTY7oN9ihPQHj\/\/aRolqunGOp1MuDHJ2SZgraeFdaS94tTBC\/+dIrwDrJmGPMIxZyWLm60AfiY4fqZ65df5NCrleGZIySA83GKWfVyx3XftqBrc1RcZxy1vJ57rXAjw3exPXTzmL42dOzIt5UfWwtrdzBsdXGIA1nsuLalgn6+bM+p6weCjhsaOwiBgZWIvOxV5HIhzo8COkeikvdxZGAqOJsJ611piLgbe3vjuD0o1645prnywu4Xk8IWcnNdlouvdMppD8ET3RxFeZKC5CJ0CTlgGr0h6UDxpzQ\/TbAVmSsGU5FYH9Etsfbk11EdXUp0A762GRQWwSMwQ1K4KFpsxONmw0Wnm8YwEVar+s06boFYwMit4WAaMz0RakKoZVqKYLh6T56hnMXSRyE3VQ6m7az2GE0tjQaV93B7QGSydhBxEK5qdjQCiRu\/fv1FCYT+eg8H\/Xg399dbu9fXiNdnkucL9M+fT\/4DGr5QbA==";

        $this->urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($orderMock);

        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentMock->expects($this->once())
            ->method('save');

        $orderMock
            ->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);
        $postRequest =
            '"PaRes": "eJzNWdnOo0qSvq+nODp9aZ3DbqDl+iWS3Sxmx\/iOzSxmsQ1me\/rBdlWdf6prZnpao1FbsoDIyMjI2L5I2Dn5PU05O40f9\/Rjp6VdF2bpb0Xy9XcURgiKgilqi1AYiVJbmt7+\/rEzGCvtXgx1l\/25JfAtvP2zK7ImTdbRIb13Rdt8IH\/Cf6I76PvjKvge52HTf+zC+AZk\/QOnMJiAd9C3x12d3mXuY4uhGEHgCPz+7aA3eQf9Nd94PO+6VdmpSD5Q2dzYNhEvNGUClDOQ5dzL8J00PPzrDnpy7JKwTz9QGIVhHKV+Q7Z\/x7G\/Y\/QOetF316c4pm4fq+wt9VzyM2W3muWeNvH8QaHbHfTjaZdO17ZJV451kz\/ud9Bfyl3D5gP+\/KOxVcCTunOOH7u+qD8rRTyVwldZL\/qu68P+0X0EO+jb3S4Oh+GDYRgAuOQoOrWUTOZsevGJF\/ADhsnrZl8suzQuPmBiVWq9vmYxVdbeiz6vn6r+Z8IOeqoCvTz6sbNXH66L3dPfprpquq+\/531\/\/TsEjeP454j92d4zaFUXhmAaWhmS1eV\/+\/09K03k5tz+r6axYdM2RRxWxRL2a4RoaZ+3yW8\/dPuVGMd6SkIgi2f\/WEX9ESN488eTAmMIscqEfi30087+mVV+VvbehX90eYg8F\/hJ0MfOSs\/pMyLS31xL\/vr7336VElyRpV3\/ryz9fdnPEr7L88LqkX6I+HHO5zLB\/HDqCuG23LgtL6BsAbqv3+e9OXfQD12\/beTttU\/WeTOasUiFzTZAM\/hAOCS4ulEgzlgs5WQPTWQdQb5GSd4DuhMOfXSzLylsk67fLuZyqsb4iocezUaK2nPtjVVP\/XagsoWY8qM7GHiq6\/SXh0L7oHzsz6ytJn0p6UJiD9o967QxtSYPJdJHN5SdqnEYGu2j\/os2hMoJ+GdCQLenPafliBbtYVcvVTqFdaVWy42q1iwpXzu05wLly0I4fWbhNTmJt2Lrbwdm7mX0JiGzXR6CUVxQlPUzfMtfKESrweYL6Zfqhc4a4RaEBmgjQJoI4lyhcJn3XS6Qwx2JIGMiOrNgB9bUvkRT6utLdSKMDYNjmeWJ+TKyoTMT6HWyJ4q2FuV4q2GtT\/Zxcom\/qKbKQebXr18+RdE3zyjp\/PbEkYBpLuzD9x2b3vvivIbzWqY0WRZyjmWBOGfMKAMmky1GZk7zaTtzYOOBOfaP7YV0xW7kzGCvtCc5H2KdMXkVmMwYO7yqMReRQVwe5BrredrEOYwKMt0DTOsA4bR3YX5SF6Z\/0zpnX52uMcpntk\/Ap+P+ERyta4QSecQCZ31GQ1+vZF5YYpQuQ1+AQ59+aLY8ykzAeabJ8VPlh0c9l0WPi1CkX+eUJxvsY0xHQp9oZF4HGsCPnMPjGhdMOsej+vMqtCtNHn+ijdmJmriF0d\/6xQ6odOIkElXq8JrGtK+9sUDjnEo\/amY3suZLD5Ef98Bz+IvGym+eXItMtFr1TeYI88Z1D0iMWXPgT9fABmYiXTJHsi6a7Y7y+JKh8tPkxrWXx5g3pzbIo9rMPGmfn0Q3c1G6S962WTTbHOXs+\/57zvas\/SrfcXmBk4VTFddVHfreReb3xPr8tO+YZXyhMbDI2jfRliOMM3nAmC7D4DLgRuY5rjDt6nOTO7QnfWotwY2hGOZvWhWSDbVaS6dyG10GklfkRyEK8bxcr8EZxPflcbw5pyqzZS25nalBlKRpKyUw2tb9Bjr7Tn2ts7DII1hV7xFBiPzkyF1Xr4mX83y77bVZZe3qFKta4Af2MbH6haeF\/EoZZakiZn1Ee1w8IowgRsK5ygyJVg7I9iGLDTHkEcH6\/OWM2ToAy8QS+bJNqaQp2nhZEXOO75srojIWIrLK1mZF18HTvSZ2NIQdqi7nowtR+ApaoGVZqRWCFBN7RaMKA+VgSdU+T4nFMzK8a827VUrYNaDayzVbKjA+pmrGhGlsBqmQetMXOxZs5b0rty1wtLCpaOx+yKi7UomPgck0wDBimWVSveYZwEtQgSy7g4wXgBmvdrdOmhCPezOQlTEAwHQljRFF0c\/hRGK26kwPAaaPavPy5yNA6V5dcyLGmFHNPs9RRLn+MaeKmv0QieMjqasl8PVcrfUhsunqOT+yifJ0lEfpGX8WXAKQjULLuBil3TwbH8zyujX3qN0aiZQmqZHj7cgxL16HMSUIMPLIcCyYsxftDGSJ1WQR0tgi29+YS17s20SyxkNBDZZPzBE6dW\/9hTJY145QeDi5qz4+\/AiwfbfOtVtZNNa\/lrfiPhKtPObaQYW9R1zTXcQS6DPXg2\/7P5QafrDpMiqR\/7EGqKJGr\/WoizCX9p62WMejxlpzhcgTsRqiWuhkQa\/iVb8AdTPzqC8Rql\/f9QjvogXxbU8HBvrksa6nuirXscqtPXTle8u4TNcYM0f+bc8KMOPIfvLNWlNH7m0r420\/k2OylNEA\/KwZCZeZ\/sq4xvRw5bKKqRruMBkpaqOHPvGMInIY4lmXJJvhtZIZNRYXgZ+uy\/BAY1915K+1Mt4X0B9xgCVY\/I6Bb3aPxf76cz1gx3c9YEyWLzEx9Fwz7GoaTkF4k4fxfEnSAQMOLyEtjh+hBzyDw6Et9F4cyMY8JrV0IwFm5oGdeEWgy+KjVedIVDXBkTej1mDWimdkUEk4r2FRtZovtxHblnjGOvKJRZWLCtWZY+vxYYRuqiXVW8vQdWd4cKbM997D4w\/V0tHODXia1o5+GhjH6CHvR9boHCTaQIiTJnYRYeSlnhNb4uJiBSGhOPZnpbi7G87QINmEAElP2lztz+0g6g58FiW5QfKIF42SLyTYVqS109jfam5g6jPabMkRMePSqBjdE8L7QmaiEcaHNn9I43Fo+oj0eOKhWBcU2KfmfDjR8iiyYhp57uDemYc482SBWdfHDvoZcX8JweKyQjBz+AuC9RwOHAAgbuJbdRGP\/ohSFvNLCA6X\/ycItsZR\/AFB+q\/Sz45QGv4GvcgKs\/MKs4hWxqNevaB3ftJeYewEo460\/77tw1qu5fLZElxu+aUQ6RFeU4QXGObAMibFPMfZTFnveeZ+qVkCNjfipTbnE0pflOmKJJtmj2P78ZiLJzIUBMQoNMKuWN42UfTmQWuhwEYpCAyqQEhINM1AB4WAPfD7tnwcZUkYy3FQZkETtpcaBMchNkPKSEhCYJEGO5\/le86XmyqJ6vUouihwbpyD+cifbtxVyi+Pph448rYggPcVntTdwBMs\/txeveJcKYV3ioO7VJDsnkmBHpDJnA21rDWSm0q4vjYXxO0MNTRnmuezNzdEvvcLwkgBRTZO1c\/UkpQF2pdkw6CbADNUxdq72IzIUZzfxKo53qYwVM4XR9W74nSjDWdUwvssbR411h\/PxAOvH9ViiQc\/R92Ne8HVpq81gY0g9ba\/+I3rpZtR5hiTAe1amrqSZZkQ\/wfIEpyC4xaqMzBmkTpEhLJUkw\/ccSTjkc8+Q1Y2rpALoGducf\/H5fQZB+N3KFzllyyAFgZtOTvT2mAMJQv+7+BMRV7QyJn+p1j0VvjGzDc8cnN2uDF4Xp5+wGqyaqLW1SP5R4gv43ocXlDn6uANs8gKd\/oKWfSQsMRzfMyCMMuCw\/pnxXM9XYLja3\/CL9sGp10O3GUIUPw7hFbms43wvfkJmW+502B4rzz9V6EWClBvXvnWFvYJq559Op7WNd+564jVknDM4eU3kwLMmVr7WG3NvWCUzJftDwAEvKBDnFK5DxWWt+mFTS43DrOw5ljiqfJfwx6gg41A030uMYd8OrVm7KynTLZtPLaPaiz3qxUOwL55hGbanvvkoPh9CQcHpyQNN1QO\/nmMo5MtgS1212q18GrydJO9JvGnAQBLRZw4i8Yw4xo1Y+6mpkhNgD5E87wCz1r5mKZZIW1\/TjaxtffarYJZOIhMcRFsKVw71PC+hdrjIutzODHHpBVhnF1QAzbqLYKtBfNaCnNQVDaErxUwRZwLNEid0wijUF3o1UUq5eUg3JY4fA5D4QFtz0VDUkp7vdDbTEoYWUf6iEFQXa\/vZGQiV7eDG0XKF1NsrWP2CBLSDQ50gXa6xyIswWAClbTXsRtQU0E3GHaDiQMK4dx6xMVr4tFtLY\/GuX8O9rj2mdrt7TvsmTweII4FQ2BQwcZ3BeTExME\/lGFXYDhw\/+kk5rkLr2tM9\/0kxnuXyjEdftBY+E2btPpfCM1J4pjwDT3aCmpv6PmcWrLwStdRymNd4+RRL2V0vU4HTkP9J6180WCNYya95Ge\/\/PfT\/Z8\/KbKDRxP+2qXlaM1OFXtWBbqTDJwVHKqI+56DIxgy6qTMUWovH7lbd3AMSUJv6r6E7\/rZk7uTBJgLzldXgew4Y+ws68D7G8RUqMgKPYXEtujdUDIJySD+csoMGc1xCTO8BV+koZeosPWqMfFONx9RbTLIhHq+jihmPcY8gCjktnXZZb7DjkGQ9MlKch5tqZo10BGvjD29xPHGxeD7DaXp+SAvS3XHJoFc7EfZC34XNnQXHItRTCBQt0qr+Q\/uKkMXfNMqnSimlUPhjAM2cD8slGIIbnawx44RK7hyb5SsKtc0dYPiJLYYmHSS7Df62CdQWVeFsFYhcelEaQLDOVSTY7oN9ihPQHj\/\/aRolqunGOp1MuDHJ2SZgraeFdaS94tTBC\/+dIrwDrJmGPMIxZyWLm60AfiY4fqZ65df5NCrleGZIySA83GKWfVyx3XftqBrc1RcZxy1vJ57rXAjw3exPXTzmL42dOzIt5UfWwtrdzBsdXGIA1nsuLalgn6+bM+p6weCjhsaOwiBgZWIvOxV5HIhzo8COkeikvdxZGAqOJsJ611piLgbe3vjuD0o1645prnywu4Xk8IWcnNdlouvdMppD8ET3RxFeZKC5CJ0CTlgGr0h6UDxpzQ\/TbAVmSsGU5FYH9Etsfbk11EdXUp0A762GRQWwSMwQ1K4KFpsxONmw0Wnm8YwEVar+s06boFYwMit4WAaMz0RakKoZVqKYLh6T56hnMXSRyE3VQ6m7az2GE0tjQaV93B7QGSydhBxEK5qdjQCiRu\/fv1FCYT+eg8H\/Xg399dbu9fXiNdnkucL9M+fT\/4DGr5QbA==",
            "PaReq": "eJxVUk1PwzAMvfMrqh24IC1p+rF0eEEdE1BgqLAJBLeQBjaJpiNt6bZfT9J1bOTkZzvP9rPhYp1\/OT9Sl8tCjXpuH\/cu2AnMF1rKyUyKWksGU1mW\/FM6y2zUI9gNKMWUhi71BoSGURT2GKTxk\/xm0BExw9MngPbQMGix4KpiwMX3OHlgPvVwgAF1EHKpkwkLPeIFge\/i3QO0c4PiuWSXhVZy45zyfHXujLnWRePczyeA2iiIolaV3jBKQkB7ALX+YouqWg0RapqmL1oOrrL39r\/BOSCbA+jQYlpbqzSc62XGSPJ4NpsFYhvRxzGZpO72o0qwHqTP\/giQzYCMV5IRTDD2CXXccOh7Qy8C1PqB57YZFlI7UAdgZWvEx5FjDxjdtVRiP80egVyvCiVNhhH3z4ZMloLN7IZSvnGmaWJKWxegwyiXN1Z7URk5iUq5P305m7\/dhYvaG19lMblu4tu4eR3ZjbRJttTSKEgIjtpaFgCyNKhbNuruwlj\/7uUXPenA8Q==",
            "MD": "",
            "DeviceID": "crMgt2ljUKvH3u++TgS2u8JI7ju4nLnfWOUd5CBamwF8\/FVUjS7AQq9d8ulTPKNM",
            "ABSlog": "GPP",
            "deviceDNA": "",
            "executionTime": "",
            "dnaError": "",
            "mesc": "",
            "mescIterationCount": "",
            "desc": "",
            "isDNADone": "false",
            "arcotFlashCookie": ""';

        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->withConsecutive(['orderId'])
            ->willReturnOnConsecutiveCalls(self::ENCODED_ORDER_ID);

        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->withConsecutive(['PaRes'], [])
            ->willReturnOnConsecutiveCalls($pares, $postRequest);

        $cryptAndCodeMock = $this
            ->getMockBuilder(CryptAndCodeData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cryptAndCodeMock
            ->expects($this->once())
            ->method('decodeAndDecrypt')
            ->with(self::ENCODED_ORDER_ID)
            ->willReturn(self::ORDER_ID);

        $this->redirectMock = $this
            ->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $messageManagerMock = $this
            ->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->makeContextMock($messageManagerMock);

        $configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $piRequestManagerDataFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $threeDCallbackManagementMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Model\PiRequestManagement\ThreeDSecureCallbackManagement')
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::CUSTOMER_ID);
        $customerRepositoryMock = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerInterfaceMock = $this
            ->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($customerInterfaceMock);
        $customerSessionMock = $this
            ->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock
            ->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerInterfaceMock)
            ->willReturnSelf();

        $this->piCallback3DController = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\PI\Callback3D',
            [
                'context'                     => $contextMock,
                'config'                      => $configMock,
                'piRequestManagerDataFactory' => $piRequestManagerDataFactoryMock,
                'requester'                   => $threeDCallbackManagementMock,
                'orderRepository'             => $orderRepositoryMock,
                'cryptAndCode'                => $cryptAndCodeMock,
                'customerSession'             => $customerSessionMock,
                'customerRepository'          => $customerRepositoryMock
            ]
        );

        $this->expectSetBody(
            '<script>window.top.location.href = "'
            . $this->urlBuilderMock->getUrl('checkout/onepage/success', ['_secure' => true])
            . '";</script>'
        );

        $this->piCallback3DController->execute();
    }

    public function testExecuteERROR()
    {
        $pares = "123456780";

        $this->urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock = $this
                ->getMockBuilder('Magento\Framework\App\Response\Http')
                ->disableOriginalConstructor()
                ->getMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($orderMock);

        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(SagePaySession::PARES_SENT, $pares);

        $this->paymentMock->expects($this->once())
            ->method('save');

        $orderMock
            ->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $postRequest =
            '"PaRes": "eJzNWdnOo0qSvq+nODp9aZ3DbqDl+iWS3Sxmx\/iOzSxmsQ1me\/rBdlWdf6prZnpao1FbsoDIyMjI2L5I2Dn5PU05O40f9\/Rjp6VdF2bpb0Xy9XcURgiKgilqi1AYiVJbmt7+\/rEzGCvtXgx1l\/25JfAtvP2zK7ImTdbRIb13Rdt8IH\/Cf6I76PvjKvge52HTf+zC+AZk\/QOnMJiAd9C3x12d3mXuY4uhGEHgCPz+7aA3eQf9Nd94PO+6VdmpSD5Q2dzYNhEvNGUClDOQ5dzL8J00PPzrDnpy7JKwTz9QGIVhHKV+Q7Z\/x7G\/Y\/QOetF316c4pm4fq+wt9VzyM2W3muWeNvH8QaHbHfTjaZdO17ZJV451kz\/ud9Bfyl3D5gP+\/KOxVcCTunOOH7u+qD8rRTyVwldZL\/qu68P+0X0EO+jb3S4Oh+GDYRgAuOQoOrWUTOZsevGJF\/ADhsnrZl8suzQuPmBiVWq9vmYxVdbeiz6vn6r+Z8IOeqoCvTz6sbNXH66L3dPfprpquq+\/531\/\/TsEjeP454j92d4zaFUXhmAaWhmS1eV\/+\/09K03k5tz+r6axYdM2RRxWxRL2a4RoaZ+3yW8\/dPuVGMd6SkIgi2f\/WEX9ESN488eTAmMIscqEfi30087+mVV+VvbehX90eYg8F\/hJ0MfOSs\/pMyLS31xL\/vr7336VElyRpV3\/ryz9fdnPEr7L88LqkX6I+HHO5zLB\/HDqCuG23LgtL6BsAbqv3+e9OXfQD12\/beTttU\/WeTOasUiFzTZAM\/hAOCS4ulEgzlgs5WQPTWQdQb5GSd4DuhMOfXSzLylsk67fLuZyqsb4iocezUaK2nPtjVVP\/XagsoWY8qM7GHiq6\/SXh0L7oHzsz6ytJn0p6UJiD9o967QxtSYPJdJHN5SdqnEYGu2j\/os2hMoJ+GdCQLenPafliBbtYVcvVTqFdaVWy42q1iwpXzu05wLly0I4fWbhNTmJt2Lrbwdm7mX0JiGzXR6CUVxQlPUzfMtfKESrweYL6Zfqhc4a4RaEBmgjQJoI4lyhcJn3XS6Qwx2JIGMiOrNgB9bUvkRT6utLdSKMDYNjmeWJ+TKyoTMT6HWyJ4q2FuV4q2GtT\/Zxcom\/qKbKQebXr18+RdE3zyjp\/PbEkYBpLuzD9x2b3vvivIbzWqY0WRZyjmWBOGfMKAMmky1GZk7zaTtzYOOBOfaP7YV0xW7kzGCvtCc5H2KdMXkVmMwYO7yqMReRQVwe5BrredrEOYwKMt0DTOsA4bR3YX5SF6Z\/0zpnX52uMcpntk\/Ap+P+ERyta4QSecQCZ31GQ1+vZF5YYpQuQ1+AQ59+aLY8ykzAeabJ8VPlh0c9l0WPi1CkX+eUJxvsY0xHQp9oZF4HGsCPnMPjGhdMOsej+vMqtCtNHn+ijdmJmriF0d\/6xQ6odOIkElXq8JrGtK+9sUDjnEo\/amY3suZLD5Ef98Bz+IvGym+eXItMtFr1TeYI88Z1D0iMWXPgT9fABmYiXTJHsi6a7Y7y+JKh8tPkxrWXx5g3pzbIo9rMPGmfn0Q3c1G6S962WTTbHOXs+\/57zvas\/SrfcXmBk4VTFddVHfreReb3xPr8tO+YZXyhMbDI2jfRliOMM3nAmC7D4DLgRuY5rjDt6nOTO7QnfWotwY2hGOZvWhWSDbVaS6dyG10GklfkRyEK8bxcr8EZxPflcbw5pyqzZS25nalBlKRpKyUw2tb9Bjr7Tn2ts7DII1hV7xFBiPzkyF1Xr4mX83y77bVZZe3qFKta4Af2MbH6haeF\/EoZZakiZn1Ee1w8IowgRsK5ygyJVg7I9iGLDTHkEcH6\/OWM2ToAy8QS+bJNqaQp2nhZEXOO75srojIWIrLK1mZF18HTvSZ2NIQdqi7nowtR+ApaoGVZqRWCFBN7RaMKA+VgSdU+T4nFMzK8a827VUrYNaDayzVbKjA+pmrGhGlsBqmQetMXOxZs5b0rty1wtLCpaOx+yKi7UomPgck0wDBimWVSveYZwEtQgSy7g4wXgBmvdrdOmhCPezOQlTEAwHQljRFF0c\/hRGK26kwPAaaPavPy5yNA6V5dcyLGmFHNPs9RRLn+MaeKmv0QieMjqasl8PVcrfUhsunqOT+yifJ0lEfpGX8WXAKQjULLuBil3TwbH8zyujX3qN0aiZQmqZHj7cgxL16HMSUIMPLIcCyYsxftDGSJ1WQR0tgi29+YS17s20SyxkNBDZZPzBE6dW\/9hTJY145QeDi5qz4+\/AiwfbfOtVtZNNa\/lrfiPhKtPObaQYW9R1zTXcQS6DPXg2\/7P5QafrDpMiqR\/7EGqKJGr\/WoizCX9p62WMejxlpzhcgTsRqiWuhkQa\/iVb8AdTPzqC8Rql\/f9QjvogXxbU8HBvrksa6nuirXscqtPXTle8u4TNcYM0f+bc8KMOPIfvLNWlNH7m0r420\/k2OylNEA\/KwZCZeZ\/sq4xvRw5bKKqRruMBkpaqOHPvGMInIY4lmXJJvhtZIZNRYXgZ+uy\/BAY1915K+1Mt4X0B9xgCVY\/I6Bb3aPxf76cz1gx3c9YEyWLzEx9Fwz7GoaTkF4k4fxfEnSAQMOLyEtjh+hBzyDw6Et9F4cyMY8JrV0IwFm5oGdeEWgy+KjVedIVDXBkTej1mDWimdkUEk4r2FRtZovtxHblnjGOvKJRZWLCtWZY+vxYYRuqiXVW8vQdWd4cKbM997D4w\/V0tHODXia1o5+GhjH6CHvR9boHCTaQIiTJnYRYeSlnhNb4uJiBSGhOPZnpbi7G87QINmEAElP2lztz+0g6g58FiW5QfKIF42SLyTYVqS109jfam5g6jPabMkRMePSqBjdE8L7QmaiEcaHNn9I43Fo+oj0eOKhWBcU2KfmfDjR8iiyYhp57uDemYc482SBWdfHDvoZcX8JweKyQjBz+AuC9RwOHAAgbuJbdRGP\/ohSFvNLCA6X\/ycItsZR\/AFB+q\/Sz45QGv4GvcgKs\/MKs4hWxqNevaB3ftJeYewEo460\/77tw1qu5fLZElxu+aUQ6RFeU4QXGObAMibFPMfZTFnveeZ+qVkCNjfipTbnE0pflOmKJJtmj2P78ZiLJzIUBMQoNMKuWN42UfTmQWuhwEYpCAyqQEhINM1AB4WAPfD7tnwcZUkYy3FQZkETtpcaBMchNkPKSEhCYJEGO5\/le86XmyqJ6vUouihwbpyD+cifbtxVyi+Pph448rYggPcVntTdwBMs\/txeveJcKYV3ioO7VJDsnkmBHpDJnA21rDWSm0q4vjYXxO0MNTRnmuezNzdEvvcLwkgBRTZO1c\/UkpQF2pdkw6CbADNUxdq72IzIUZzfxKo53qYwVM4XR9W74nSjDWdUwvssbR411h\/PxAOvH9ViiQc\/R92Ne8HVpq81gY0g9ba\/+I3rpZtR5hiTAe1amrqSZZkQ\/wfIEpyC4xaqMzBmkTpEhLJUkw\/ccSTjkc8+Q1Y2rpALoGducf\/H5fQZB+N3KFzllyyAFgZtOTvT2mAMJQv+7+BMRV7QyJn+p1j0VvjGzDc8cnN2uDF4Xp5+wGqyaqLW1SP5R4gv43ocXlDn6uANs8gKd\/oKWfSQsMRzfMyCMMuCw\/pnxXM9XYLja3\/CL9sGp10O3GUIUPw7hFbms43wvfkJmW+502B4rzz9V6EWClBvXvnWFvYJq559Op7WNd+564jVknDM4eU3kwLMmVr7WG3NvWCUzJftDwAEvKBDnFK5DxWWt+mFTS43DrOw5ljiqfJfwx6gg41A030uMYd8OrVm7KynTLZtPLaPaiz3qxUOwL55hGbanvvkoPh9CQcHpyQNN1QO\/nmMo5MtgS1212q18GrydJO9JvGnAQBLRZw4i8Yw4xo1Y+6mpkhNgD5E87wCz1r5mKZZIW1\/TjaxtffarYJZOIhMcRFsKVw71PC+hdrjIutzODHHpBVhnF1QAzbqLYKtBfNaCnNQVDaErxUwRZwLNEid0wijUF3o1UUq5eUg3JY4fA5D4QFtz0VDUkp7vdDbTEoYWUf6iEFQXa\/vZGQiV7eDG0XKF1NsrWP2CBLSDQ50gXa6xyIswWAClbTXsRtQU0E3GHaDiQMK4dx6xMVr4tFtLY\/GuX8O9rj2mdrt7TvsmTweII4FQ2BQwcZ3BeTExME\/lGFXYDhw\/+kk5rkLr2tM9\/0kxnuXyjEdftBY+E2btPpfCM1J4pjwDT3aCmpv6PmcWrLwStdRymNd4+RRL2V0vU4HTkP9J6180WCNYya95Ge\/\/PfT\/Z8\/KbKDRxP+2qXlaM1OFXtWBbqTDJwVHKqI+56DIxgy6qTMUWovH7lbd3AMSUJv6r6E7\/rZk7uTBJgLzldXgew4Y+ws68D7G8RUqMgKPYXEtujdUDIJySD+csoMGc1xCTO8BV+koZeosPWqMfFONx9RbTLIhHq+jihmPcY8gCjktnXZZb7DjkGQ9MlKch5tqZo10BGvjD29xPHGxeD7DaXp+SAvS3XHJoFc7EfZC34XNnQXHItRTCBQt0qr+Q\/uKkMXfNMqnSimlUPhjAM2cD8slGIIbnawx44RK7hyb5SsKtc0dYPiJLYYmHSS7Df62CdQWVeFsFYhcelEaQLDOVSTY7oN9ihPQHj\/\/aRolqunGOp1MuDHJ2SZgraeFdaS94tTBC\/+dIrwDrJmGPMIxZyWLm60AfiY4fqZ65df5NCrleGZIySA83GKWfVyx3XftqBrc1RcZxy1vJ57rXAjw3exPXTzmL42dOzIt5UfWwtrdzBsdXGIA1nsuLalgn6+bM+p6weCjhsaOwiBgZWIvOxV5HIhzo8COkeikvdxZGAqOJsJ611piLgbe3vjuD0o1645prnywu4Xk8IWcnNdlouvdMppD8ET3RxFeZKC5CJ0CTlgGr0h6UDxpzQ\/TbAVmSsGU5FYH9Etsfbk11EdXUp0A762GRQWwSMwQ1K4KFpsxONmw0Wnm8YwEVar+s06boFYwMit4WAaMz0RakKoZVqKYLh6T56hnMXSRyE3VQ6m7az2GE0tjQaV93B7QGSydhBxEK5qdjQCiRu\/fv1FCYT+eg8H\/Xg399dbu9fXiNdnkucL9M+fT\/4DGr5QbA==",
            "PaReq": "eJxVUk1PwzAMvfMrqh24IC1p+rF0eEEdE1BgqLAJBLeQBjaJpiNt6bZfT9J1bOTkZzvP9rPhYp1\/OT9Sl8tCjXpuH\/cu2AnMF1rKyUyKWksGU1mW\/FM6y2zUI9gNKMWUhi71BoSGURT2GKTxk\/xm0BExw9MngPbQMGix4KpiwMX3OHlgPvVwgAF1EHKpkwkLPeIFge\/i3QO0c4PiuWSXhVZy45zyfHXujLnWRePczyeA2iiIolaV3jBKQkB7ALX+YouqWg0RapqmL1oOrrL39r\/BOSCbA+jQYlpbqzSc62XGSPJ4NpsFYhvRxzGZpO72o0qwHqTP\/giQzYCMV5IRTDD2CXXccOh7Qy8C1PqB57YZFlI7UAdgZWvEx5FjDxjdtVRiP80egVyvCiVNhhH3z4ZMloLN7IZSvnGmaWJKWxegwyiXN1Z7URk5iUq5P305m7\/dhYvaG19lMblu4tu4eR3ZjbRJttTSKEgIjtpaFgCyNKhbNuruwlj\/7uUXPenA8Q==",
            "MD": "",
            "DeviceID": "crMgt2ljUKvH3u++TgS2u8JI7ju4nLnfWOUd5CBamwF8\/FVUjS7AQq9d8ulTPKNM",
            "ABSlog": "GPP",
            "deviceDNA": "",
            "executionTime": "",
            "dnaError": "",
            "mesc": "",
            "mescIterationCount": "",
            "desc": "",
            "isDNADone": "false",
            "arcotFlashCookie": ""';

        $this->makeRequestMock($pares, $postRequest);

        $inSessionPares = "987654321";

        $suiteLoggerMock = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutSessionMock = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutSessionMock
            ->expects($this->once())
            ->method('setData')
            ->with(\Ebizmarts\SagePaySuite\Model\Session::PARES_SENT, $pares)
            ->willReturnSelf();

        $cryptAndCodeMock = $this
            ->getMockBuilder(CryptAndCodeData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cryptAndCodeMock
            ->expects($this->once())
            ->method('decodeAndDecrypt')
            ->with(self::ENCODED_ORDER_ID)
            ->willReturn(self::ORDER_ID);

        $this->redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $messageManagerMock = $this->getMockBuilder('Magento\Framework\Message\ManagerInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $messageManagerMock->expects($this->once())->method('addError')->with('Invalid 3D secure authentication.');

        $contextMock = $this->makeContextMock($messageManagerMock);

        $configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $piRequestManagerMock = $this->makeRequestManagerMock($pares);

        $piRequestManagerDataFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $piRequestManagerDataFactoryMock->expects($this->once())->method('create')->willReturn($piRequestManagerMock);

        $resultMock = $this->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiResult')
            ->disableOriginalConstructor()
            ->getMock();
        $resultMock
            ->expects($this->exactly(2))->method('getErrorMessage')->willReturn('Invalid 3D secure authentication.');

        $threeDCallbackManagementMock = $this->makeThreeDCallbackManagementMock($resultMock);

        $orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::CUSTOMER_ID);
        $customerRepositoryMock = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerInterfaceMock = $this
            ->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($customerInterfaceMock);
        $customerSessionMock = $this
            ->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock
            ->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerInterfaceMock)
            ->willReturnSelf();

        $this->piCallback3DController = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\PI\Callback3D',
            [
                'context'                     => $contextMock,
                'config'                      => $configMock,
                'piRequestManagerDataFactory' => $piRequestManagerDataFactoryMock,
                'requester'                   => $threeDCallbackManagementMock,
                'orderRepository'             => $orderRepositoryMock,
                'cryptAndCode'                => $cryptAndCodeMock,
                'checkoutSession'             => $checkoutSessionMock,
                'suiteLogger'                 => $suiteLoggerMock,
                'customerSession'             => $customerSessionMock,
                'customerRepository'          => $customerRepositoryMock
            ]
        );
        $this->expectSetBody(
            '<script>window.top.location.href = "'
            . $this->urlBuilderMock->getUrl('checkout/cart', ['_secure' => true])
            . '";</script>'
        );

        $this->piCallback3DController->execute();
    }

    /**
     * @param $body
     */
    private function expectSetBody($body)
    {
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($body);
    }

    public function testSuccessInvalid3d()
    {
        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $pares = "123456780";

        $postRequest =
            '"PaRes": "eJzNWdnOo0qSvq+nODp9aZ3DbqDl+iWS3Sxmx\/iOzSxmsQ1me\/rBdlWdf6prZnpao1FbsoDIyMjI2L5I2Dn5PU05O40f9\/Rjp6VdF2bpb0Xy9XcURgiKgilqi1AYiVJbmt7+\/rEzGCvtXgx1l\/25JfAtvP2zK7ImTdbRIb13Rdt8IH\/Cf6I76PvjKvge52HTf+zC+AZk\/QOnMJiAd9C3x12d3mXuY4uhGEHgCPz+7aA3eQf9Nd94PO+6VdmpSD5Q2dzYNhEvNGUClDOQ5dzL8J00PPzrDnpy7JKwTz9QGIVhHKV+Q7Z\/x7G\/Y\/QOetF316c4pm4fq+wt9VzyM2W3muWeNvH8QaHbHfTjaZdO17ZJV451kz\/ud9Bfyl3D5gP+\/KOxVcCTunOOH7u+qD8rRTyVwldZL\/qu68P+0X0EO+jb3S4Oh+GDYRgAuOQoOrWUTOZsevGJF\/ADhsnrZl8suzQuPmBiVWq9vmYxVdbeiz6vn6r+Z8IOeqoCvTz6sbNXH66L3dPfprpquq+\/531\/\/TsEjeP454j92d4zaFUXhmAaWhmS1eV\/+\/09K03k5tz+r6axYdM2RRxWxRL2a4RoaZ+3yW8\/dPuVGMd6SkIgi2f\/WEX9ESN488eTAmMIscqEfi30087+mVV+VvbehX90eYg8F\/hJ0MfOSs\/pMyLS31xL\/vr7336VElyRpV3\/ryz9fdnPEr7L88LqkX6I+HHO5zLB\/HDqCuG23LgtL6BsAbqv3+e9OXfQD12\/beTttU\/WeTOasUiFzTZAM\/hAOCS4ulEgzlgs5WQPTWQdQb5GSd4DuhMOfXSzLylsk67fLuZyqsb4iocezUaK2nPtjVVP\/XagsoWY8qM7GHiq6\/SXh0L7oHzsz6ytJn0p6UJiD9o967QxtSYPJdJHN5SdqnEYGu2j\/os2hMoJ+GdCQLenPafliBbtYVcvVTqFdaVWy42q1iwpXzu05wLly0I4fWbhNTmJt2Lrbwdm7mX0JiGzXR6CUVxQlPUzfMtfKESrweYL6Zfqhc4a4RaEBmgjQJoI4lyhcJn3XS6Qwx2JIGMiOrNgB9bUvkRT6utLdSKMDYNjmeWJ+TKyoTMT6HWyJ4q2FuV4q2GtT\/Zxcom\/qKbKQebXr18+RdE3zyjp\/PbEkYBpLuzD9x2b3vvivIbzWqY0WRZyjmWBOGfMKAMmky1GZk7zaTtzYOOBOfaP7YV0xW7kzGCvtCc5H2KdMXkVmMwYO7yqMReRQVwe5BrredrEOYwKMt0DTOsA4bR3YX5SF6Z\/0zpnX52uMcpntk\/Ap+P+ERyta4QSecQCZ31GQ1+vZF5YYpQuQ1+AQ59+aLY8ykzAeabJ8VPlh0c9l0WPi1CkX+eUJxvsY0xHQp9oZF4HGsCPnMPjGhdMOsej+vMqtCtNHn+ijdmJmriF0d\/6xQ6odOIkElXq8JrGtK+9sUDjnEo\/amY3suZLD5Ef98Bz+IvGym+eXItMtFr1TeYI88Z1D0iMWXPgT9fABmYiXTJHsi6a7Y7y+JKh8tPkxrWXx5g3pzbIo9rMPGmfn0Q3c1G6S962WTTbHOXs+\/57zvas\/SrfcXmBk4VTFddVHfreReb3xPr8tO+YZXyhMbDI2jfRliOMM3nAmC7D4DLgRuY5rjDt6nOTO7QnfWotwY2hGOZvWhWSDbVaS6dyG10GklfkRyEK8bxcr8EZxPflcbw5pyqzZS25nalBlKRpKyUw2tb9Bjr7Tn2ts7DII1hV7xFBiPzkyF1Xr4mX83y77bVZZe3qFKta4Af2MbH6haeF\/EoZZakiZn1Ee1w8IowgRsK5ygyJVg7I9iGLDTHkEcH6\/OWM2ToAy8QS+bJNqaQp2nhZEXOO75srojIWIrLK1mZF18HTvSZ2NIQdqi7nowtR+ApaoGVZqRWCFBN7RaMKA+VgSdU+T4nFMzK8a827VUrYNaDayzVbKjA+pmrGhGlsBqmQetMXOxZs5b0rty1wtLCpaOx+yKi7UomPgck0wDBimWVSveYZwEtQgSy7g4wXgBmvdrdOmhCPezOQlTEAwHQljRFF0c\/hRGK26kwPAaaPavPy5yNA6V5dcyLGmFHNPs9RRLn+MaeKmv0QieMjqasl8PVcrfUhsunqOT+yifJ0lEfpGX8WXAKQjULLuBil3TwbH8zyujX3qN0aiZQmqZHj7cgxL16HMSUIMPLIcCyYsxftDGSJ1WQR0tgi29+YS17s20SyxkNBDZZPzBE6dW\/9hTJY145QeDi5qz4+\/AiwfbfOtVtZNNa\/lrfiPhKtPObaQYW9R1zTXcQS6DPXg2\/7P5QafrDpMiqR\/7EGqKJGr\/WoizCX9p62WMejxlpzhcgTsRqiWuhkQa\/iVb8AdTPzqC8Rql\/f9QjvogXxbU8HBvrksa6nuirXscqtPXTle8u4TNcYM0f+bc8KMOPIfvLNWlNH7m0r420\/k2OylNEA\/KwZCZeZ\/sq4xvRw5bKKqRruMBkpaqOHPvGMInIY4lmXJJvhtZIZNRYXgZ+uy\/BAY1915K+1Mt4X0B9xgCVY\/I6Bb3aPxf76cz1gx3c9YEyWLzEx9Fwz7GoaTkF4k4fxfEnSAQMOLyEtjh+hBzyDw6Et9F4cyMY8JrV0IwFm5oGdeEWgy+KjVedIVDXBkTej1mDWimdkUEk4r2FRtZovtxHblnjGOvKJRZWLCtWZY+vxYYRuqiXVW8vQdWd4cKbM997D4w\/V0tHODXia1o5+GhjH6CHvR9boHCTaQIiTJnYRYeSlnhNb4uJiBSGhOPZnpbi7G87QINmEAElP2lztz+0g6g58FiW5QfKIF42SLyTYVqS109jfam5g6jPabMkRMePSqBjdE8L7QmaiEcaHNn9I43Fo+oj0eOKhWBcU2KfmfDjR8iiyYhp57uDemYc482SBWdfHDvoZcX8JweKyQjBz+AuC9RwOHAAgbuJbdRGP\/ohSFvNLCA6X\/ycItsZR\/AFB+q\/Sz45QGv4GvcgKs\/MKs4hWxqNevaB3ftJeYewEo460\/77tw1qu5fLZElxu+aUQ6RFeU4QXGObAMibFPMfZTFnveeZ+qVkCNjfipTbnE0pflOmKJJtmj2P78ZiLJzIUBMQoNMKuWN42UfTmQWuhwEYpCAyqQEhINM1AB4WAPfD7tnwcZUkYy3FQZkETtpcaBMchNkPKSEhCYJEGO5\/le86XmyqJ6vUouihwbpyD+cifbtxVyi+Pph448rYggPcVntTdwBMs\/txeveJcKYV3ioO7VJDsnkmBHpDJnA21rDWSm0q4vjYXxO0MNTRnmuezNzdEvvcLwkgBRTZO1c\/UkpQF2pdkw6CbADNUxdq72IzIUZzfxKo53qYwVM4XR9W74nSjDWdUwvssbR411h\/PxAOvH9ViiQc\/R92Ne8HVpq81gY0g9ba\/+I3rpZtR5hiTAe1amrqSZZkQ\/wfIEpyC4xaqMzBmkTpEhLJUkw\/ccSTjkc8+Q1Y2rpALoGducf\/H5fQZB+N3KFzllyyAFgZtOTvT2mAMJQv+7+BMRV7QyJn+p1j0VvjGzDc8cnN2uDF4Xp5+wGqyaqLW1SP5R4gv43ocXlDn6uANs8gKd\/oKWfSQsMRzfMyCMMuCw\/pnxXM9XYLja3\/CL9sGp10O3GUIUPw7hFbms43wvfkJmW+502B4rzz9V6EWClBvXvnWFvYJq559Op7WNd+564jVknDM4eU3kwLMmVr7WG3NvWCUzJftDwAEvKBDnFK5DxWWt+mFTS43DrOw5ljiqfJfwx6gg41A030uMYd8OrVm7KynTLZtPLaPaiz3qxUOwL55hGbanvvkoPh9CQcHpyQNN1QO\/nmMo5MtgS1212q18GrydJO9JvGnAQBLRZw4i8Yw4xo1Y+6mpkhNgD5E87wCz1r5mKZZIW1\/TjaxtffarYJZOIhMcRFsKVw71PC+hdrjIutzODHHpBVhnF1QAzbqLYKtBfNaCnNQVDaErxUwRZwLNEid0wijUF3o1UUq5eUg3JY4fA5D4QFtz0VDUkp7vdDbTEoYWUf6iEFQXa\/vZGQiV7eDG0XKF1NsrWP2CBLSDQ50gXa6xyIswWAClbTXsRtQU0E3GHaDiQMK4dx6xMVr4tFtLY\/GuX8O9rj2mdrt7TvsmTweII4FQ2BQwcZ3BeTExME\/lGFXYDhw\/+kk5rkLr2tM9\/0kxnuXyjEdftBY+E2btPpfCM1J4pjwDT3aCmpv6PmcWrLwStdRymNd4+RRL2V0vU4HTkP9J6180WCNYya95Ge\/\/PfT\/Z8\/KbKDRxP+2qXlaM1OFXtWBbqTDJwVHKqI+56DIxgy6qTMUWovH7lbd3AMSUJv6r6E7\/rZk7uTBJgLzldXgew4Y+ws68D7G8RUqMgKPYXEtujdUDIJySD+csoMGc1xCTO8BV+koZeosPWqMfFONx9RbTLIhHq+jihmPcY8gCjktnXZZb7DjkGQ9MlKch5tqZo10BGvjD29xPHGxeD7DaXp+SAvS3XHJoFc7EfZC34XNnQXHItRTCBQt0qr+Q\/uKkMXfNMqnSimlUPhjAM2cD8slGIIbnawx44RK7hyb5SsKtc0dYPiJLYYmHSS7Df62CdQWVeFsFYhcelEaQLDOVSTY7oN9ihPQHj\/\/aRolqunGOp1MuDHJ2SZgraeFdaS94tTBC\/+dIrwDrJmGPMIxZyWLm60AfiY4fqZ65df5NCrleGZIySA83GKWfVyx3XftqBrc1RcZxy1vJ57rXAjw3exPXTzmL42dOzIt5UfWwtrdzBsdXGIA1nsuLalgn6+bM+p6weCjhsaOwiBgZWIvOxV5HIhzo8COkeikvdxZGAqOJsJ611piLgbe3vjuD0o1645prnywu4Xk8IWcnNdlouvdMppD8ET3RxFeZKC5CJ0CTlgGr0h6UDxpzQ\/TbAVmSsGU5FYH9Etsfbk11EdXUp0A762GRQWwSMwQ1K4KFpsxONmw0Wnm8YwEVar+s06boFYwMit4WAaMz0RakKoZVqKYLh6T56hnMXSRyE3VQ6m7az2GE0tjQaV93B7QGSydhBxEK5qdjQCiRu\/fv1FCYT+eg8H\/Xg399dbu9fXiNdnkucL9M+fT\/4DGr5QbA==",
            "PaReq": "eJxVUk1PwzAMvfMrqh24IC1p+rF0eEEdE1BgqLAJBLeQBjaJpiNt6bZfT9J1bOTkZzvP9rPhYp1\/OT9Sl8tCjXpuH\/cu2AnMF1rKyUyKWksGU1mW\/FM6y2zUI9gNKMWUhi71BoSGURT2GKTxk\/xm0BExw9MngPbQMGix4KpiwMX3OHlgPvVwgAF1EHKpkwkLPeIFge\/i3QO0c4PiuWSXhVZy45zyfHXujLnWRePczyeA2iiIolaV3jBKQkB7ALX+YouqWg0RapqmL1oOrrL39r\/BOSCbA+jQYlpbqzSc62XGSPJ4NpsFYhvRxzGZpO72o0qwHqTP\/giQzYCMV5IRTDD2CXXccOh7Qy8C1PqB57YZFlI7UAdgZWvEx5FjDxjdtVRiP80egVyvCiVNhhH3z4ZMloLN7IZSvnGmaWJKWxegwyiXN1Z7URk5iUq5P305m7\/dhYvaG19lMblu4tu4eR3ZjbRJttTSKEgIjtpaFgCyNKhbNuruwlj\/7uUXPenA8Q==",
            "MD": "",
            "DeviceID": "crMgt2ljUKvH3u++TgS2u8JI7ju4nLnfWOUd5CBamwF8\/FVUjS7AQq9d8ulTPKNM",
            "ABSlog": "GPP",
            "deviceDNA": "",
            "executionTime": "",
            "dnaError": "",
            "mesc": "",
            "mescIterationCount": "",
            "desc": "",
            "isDNADone": "false",
            "arcotFlashCookie": ""';

        $this->makeRequestMock($pares, $postRequest);

        $inSessionPares = "987654321";

        $suiteLoggerMock = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutSessionMock = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutSessionMock
            ->expects($this->once())
            ->method('setData')
            ->with(\Ebizmarts\SagePaySuite\Model\Session::PARES_SENT, $pares)
            ->willReturnSelf();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $cryptAndCodeMock = $this
            ->getMockBuilder(CryptAndCodeData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cryptAndCodeMock
            ->expects($this->once())
            ->method('decodeAndDecrypt')
            ->with(self::ENCODED_ORDER_ID)
            ->willReturn(self::ORDER_ID);

        $orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($orderMock);

        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(SagePaySession::PARES_SENT, $pares);

        $this->paymentMock->expects($this->once())
            ->method('save');

        $this->paymentMock->expects($this->once())
            ->method('setAdditionalInformation')
            ->with(SagePaySession::PARES_SENT, $pares)
            ->willReturn(null);

        $orderMock
            ->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PENDING_PAYMENT);

        $this->redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $messageManagerMock
            ->expects($this->once())
            ->method('addError')
            ->with("Invalid 3D secure authentication.");

        $this->urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->makeContextMock($messageManagerMock);

        $configMock = $this
            ->getMockBuilder('Ebizmarts\SagePaySuite\Model\Config')
            ->disableOriginalConstructor()
            ->getMock();

        $resultMock = $this->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiResult')
            ->disableOriginalConstructor()
            ->getMock();
        $resultMock
            ->expects($this->exactly(2))->method('getErrorMessage')->willReturn('Invalid 3D secure authentication.');

        $threeDCallbackManagementMock = $this->makeThreeDCallbackManagementMock($resultMock);

        $piRequestManagerDataFactoryMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiRequestManagerFactory')
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $piRequestManagerDataFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->makeRequestManagerMock($pares));

        $orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::CUSTOMER_ID);
        $customerRepositoryMock = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerInterfaceMock = $this
            ->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($customerInterfaceMock);
        $customerSessionMock = $this
            ->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock
            ->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerInterfaceMock)
            ->willReturnSelf();

        $controller = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\PI\Callback3D',
            [
                'context'                     => $contextMock,
                'config'                      => $configMock,
                'piRequestManagerDataFactory' => $piRequestManagerDataFactoryMock,
                'requester'                   => $threeDCallbackManagementMock,
                'orderRepository'             => $orderRepositoryMock,
                'cryptAndCode'                => $cryptAndCodeMock,
                'checkoutSession'             => $checkoutSessionMock,
                'suiteLogger'                 => $suiteLoggerMock,
                'customerSession'             => $customerSessionMock,
                'customerRepository'          => $customerRepositoryMock
            ]
        );

        $this->expectSetBody(
            '<script>window.top.location.href = "'
            . $this->urlBuilderMock->getUrl('checkout/cart', ['_secure' => true])
            . '";</script>'
        );

        $controller->execute();
    }

    public function testDuplicatedCallbacks()
    {
        $this->responseMock = $this
            ->getMockBuilder('Magento\Framework\App\Response\Http')
            ->disableOriginalConstructor()
            ->getMock();

        $pares = "123456780";

        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->with('PaRes')
            ->willReturn($pares);

        $this->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->withConsecutive(['orderId'])
            ->willReturnOnConsecutiveCalls(self::ENCODED_ORDER_ID);

        $cryptAndCodeMock = $this
            ->getMockBuilder(CryptAndCodeData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cryptAndCodeMock
            ->expects($this->once())
            ->method('decodeAndDecrypt')
            ->with(self::ENCODED_ORDER_ID)
            ->willReturn(self::ORDER_ID);

        $inSessionPares = "123456780";

        $message = Callback3D::DUPLICATED_CALLBACK_ERROR_MESSAGE . ' OrderId: ' . self::ORDER_ID . ' VPSTxId: ' . self::TEST_VPSTXID;

        $suiteLoggerMock = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $checkoutSessionMock = $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock = $this
            ->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderMock = $this
            ->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $orderRepositoryMock
            ->expects($this->once())
            ->method('get')
            ->with(self::ORDER_ID)
            ->willReturn($orderMock);

        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(SagePaySession::PARES_SENT)
            ->willReturn($pares);

        $orderMock
            ->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->redirectMock = $this->getMockForAbstractClass('Magento\Framework\App\Response\RedirectInterface');

        $messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilderMock = $this
            ->getMockBuilder('Magento\Framework\UrlInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $contextMock = $this->makeContextMock($messageManagerMock);

        $orderMock
            ->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(self::CUSTOMER_ID);
        $customerRepositoryMock = $this
            ->getMockBuilder(CustomerRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerInterfaceMock = $this
            ->getMockBuilder(CustomerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepositoryMock
            ->expects($this->once())
            ->method('getById')
            ->with(self::CUSTOMER_ID)
            ->willReturn($customerInterfaceMock);
        $customerSessionMock = $this
            ->getMockBuilder(CustomerSession::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSessionMock
            ->expects($this->once())
            ->method('setCustomerDataAsLoggedIn')
            ->with($customerInterfaceMock)
            ->willReturnSelf();

        $controller = $this->objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Controller\PI\Callback3D',
            [
                'context'                     => $contextMock,
                'orderRepository'             => $orderRepositoryMock,
                'cryptAndCode'                => $cryptAndCodeMock,
                'checkoutSession'             => $checkoutSessionMock,
                'suiteLogger'                 => $suiteLoggerMock,
                'customerSession'             => $customerSessionMock,
                'customerRepository'          => $customerRepositoryMock
            ]
        );

        $controller->execute();
    }

    /**
     * @param $messageManagerMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeContextMock($messageManagerMock)
    {
        $contextMock = $this
            ->getMockBuilder('Magento\Framework\App\Action\Context')->disableOriginalConstructor()->getMock();
        $contextMock->expects($this->any())->method('getRequest')->will($this->returnValue($this->requestMock));
        $contextMock->expects($this->any())->method('getResponse')->will($this->returnValue($this->responseMock));
        $contextMock->expects($this->any())->method('getRedirect')->will($this->returnValue($this->redirectMock));
        $contextMock->expects($this->any())->method('getMessageManager')->will($this->returnValue($messageManagerMock));
        $contextMock->expects($this->any())->method('getUrl')->will($this->returnValue($this->urlBuilderMock));

        return $contextMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeRequestManagerMock($pares)
    {
        $piRequestManagerMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Api\Data\PiRequestManager')
            ->disableOriginalConstructor()->getMock();
        $piRequestManagerMock->expects($this->once())->method('setTransactionId');
        $piRequestManagerMock->expects($this->once())->method('setParEs')->with($pares);
        $piRequestManagerMock->expects($this->once())->method('setVendorName');
        $piRequestManagerMock->expects($this->once())->method('setMode');
        $piRequestManagerMock->expects($this->once())->method('setPaymentAction');

        return $piRequestManagerMock;
    }

    /**
     * @param $resultMock
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function makeThreeDCallbackManagementMock($resultMock)
    {
        $threeDCallbackManagementMock = $this
            ->getMockBuilder('\Ebizmarts\SagePaySuite\Model\PiRequestManagement\ThreeDSecureCallbackManagement')
            ->disableOriginalConstructor()->getMock();
        $threeDCallbackManagementMock->expects($this->once())->method('setRequestData');
        $threeDCallbackManagementMock->expects($this->once())->method('placeOrder')->willReturn($resultMock);

        return $threeDCallbackManagementMock;
    }

    private function makeRequestMock($pares, $postRequest)
    {
        $this->requestMock = $this
            ->getMockBuilder('Magento\Framework\HTTP\PhpEnvironment\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock
            ->expects($this->exactly(2))
            ->method('getParam')
            ->withConsecutive(['orderId'], ['transactionId'])
            ->willReturnOnConsecutiveCalls(
                self::ENCODED_ORDER_ID,
                $this->returnValue(self::TEST_VPSTXID)
            );
        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->withConsecutive(['PaRes'], [])
            ->willReturnOnConsecutiveCalls($pares, $postRequest);
    }
}
