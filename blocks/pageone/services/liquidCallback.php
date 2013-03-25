<?php
require_once('../../../config.php');
require_once('callback_url.php');
global $CFG;
header("Content-Type: application/wsdl+xml");
?>
<!-- edited with XMLSpy v2008 sp1 (http://www.altova.com) by Simple (Simon) -->
<!--xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" -->
<wsdl:definitions xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:intf="http://jaxb.liquidcallback.pageone.com" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:enc="http://schemas.xmlsoap.org/soap/encoding/" targetNamespace="http://jaxb.liquidcallback.pageone.com">
	<wsdl:types>
		<xsd:schema xmlns="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified" targetNamespace="http://jaxb.liquidcallback.pageone.com">
			<xsd:element name="onDeliveryReport">
				<xsd:complexType>
					<xsd:sequence>
						<xsd:element name="source" type="xsd:string"/>
						<xsd:element name="destination" type="xsd:string"/>
						<xsd:element name="receiptTime" type="xsd:dateTime"/>
						<xsd:element name="resultCode" type="xsd:int" minOccurs="0"/>
					</xsd:sequence>
					<xsd:attribute name="transactionID" type="xsd:string" use="optional"/>
				</xsd:complexType>
			</xsd:element>
			<xsd:element name="onMessageReceived">
				<xsd:complexType>
					<xsd:sequence>
						<xsd:element name="source" type="xsd:string"/>
						<xsd:element name="destination" type="xsd:string"/>
						<xsd:element name="messageTime" type="xsd:dateTime"/>
						<xsd:element name="text" type="xsd:string" minOccurs="0"/>
					</xsd:sequence>
					<xsd:attribute name="transactionID" type="xsd:string" use="optional"/>
				</xsd:complexType>
			</xsd:element>
		</xsd:schema>
	</wsdl:types>
	<wsdl:message name="onDeliveryReportRequest">
		<wsdl:part name="receipt" element="intf:onDeliveryReport"/>
	</wsdl:message>
	<wsdl:message name="onMessageReceivedRequest">
		<wsdl:part name="message" element="intf:onMessageReceived"/>
	</wsdl:message>
	<wsdl:portType name="CallbackServicePortType">
		<wsdl:operation name="onDeliveryReport">
			<wsdl:input name="onDeliveryReportRequest" message="intf:onDeliveryReportRequest"/>
		</wsdl:operation>
		<wsdl:operation name="onMessageReceived">
			<wsdl:input name="onMessageReceivedRequest" message="intf:onMessageReceivedRequest"/>
		</wsdl:operation>
	</wsdl:portType>
	<wsdl:binding name="CallbackSoapBinding" type="intf:CallbackServicePortType">
		<soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"/>
		<wsdl:operation name="onDeliveryReport">
			<soap:operation soapAction="onDeliveryReport" style="document"/>
			<wsdl:input name="onDeliveryReportRequest">
				<soap:body use="literal"/>
			</wsdl:input>
		</wsdl:operation>
		<wsdl:operation name="onMessageReceived">
			<soap:operation soapAction="onMessageRecevied" style="document"/>
			<wsdl:input name="onMessageReceivedRequest">
				<soap:body use="literal"/>
			</wsdl:input>
		</wsdl:operation>
	</wsdl:binding>
	<wsdl:service name="CallbackService">
		<wsdl:port name="CallbackPort" binding="intf:CallbackSoapBinding">
			<soap:address location="<?php
                            global $CFG;
                            if ($CFG->block_pageone_https==true && strpos($CALLBACK_URL, "http://")>-1)
                                echo str_replace("http://", "https://", $CALLBACK_URL);
                            else
                                echo $CALLBACK_URL;?>"/>
		</wsdl:port>
	</wsdl:service>
</wsdl:definitions>
