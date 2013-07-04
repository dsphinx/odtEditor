<?php

error_reporting(E_ALL);

class PHPParser {
	
	public $path;
	public $reader;
	public $c = 1;
	public $z = 1;
	public $content;
	
	public function __construct ($prefix, $filename, $path) {
		$this->reader = new XMLReader;
		$this->filename = $filename;
		$this->prefix = $prefix;
		$this->path = $path;
	}
	
	private function unzip() {
		$zip = new ZipArchive;
		if ($zip->open($this->prefix.'/'.$this->filename) === TRUE) {
			$zip->extractTo($this->path.'/'.$this->filename);
			$zip->close();
		}
	}
	
	private function zip() {
		$zip = new ZipArchive();
		$filename = $this->prefix.'/'.$this->filename;
		if ($zip->open($filename, ZIPARCHIVE::CREATE)!==TRUE) {
			return "Error 100";
		}
		$zip->addFile($this->path.'/'.$this->filename.'/content.xml','content.xml');
		$zip->addFile($this->path.'/'.$this->filename.'/styles.xml','styles.xml');
		$zip->addFile($this->path.'/'.$this->filename.'/meta.xml','meta.xml');
		$zip->addFile($this->path.'/'.$this->filename.'/settings.xml','settings.xml');
		$zip->addFile($this->path.'/'.$this->filename.'/META-INF/manifest.xml','META-INF/manifest.xml');
		$zip->addFile($this->path.'/'.$this->filename.'/mimetype','mimetype');
		$zip->close();
	}
	
	public function attributes ($reader) {
		$content = " ";
		if($reader->getAttribute("style:num-format")) {
			if($this->z != 2)
				$content .= "list-style-type:decimal;";
			$this->z = 2;
		} elseif($reader->getAttribute("text:bullet-char")) {
			if($this->z != 2)
				$content .= "list-style-type:disc;";
			$this->z = 2;
		}
		if($reader->getAttribute("fo:margin-left")) {
			if($this->c != 2)
				$content .= "margin-left:".$reader->getAttribute("fo:margin-left").";";
			$this->c = 2;
		}
		if($reader->getAttribute("fo:font-weight"))
			$content .= "font-weight:".$reader->getAttribute("fo:font-weight").";";
		if($reader->getAttribute("fo:font-style"))
			$content .= "font-style:".$reader->getAttribute("fo:font-style").";";
		if($reader->getAttribute("style:text-line-through-style"))
			$content .= "text-decoration:line-through;";
		if($reader->getAttribute("fo:font-size"))
			$content .= "font-size:".$reader->getAttribute("fo:font-size").";";
		if($reader->getAttribute("fo:margin-right"))
			$content .= "margin-right:".$reader->getAttribute("fo:margin-right").";";
		if($reader->getAttribute("fo:line-height"))
			$content .= "line-height:".$reader->getAttribute("fo:line-height").";";
		if($reader->getAttribute("fo:background-color"))
			$content .= "background-color:".$reader->getAttribute("fo:background-color").";";
		if($reader->getAttribute("fo:break-before"))
			$content .= "page-break-before:always;text-align:left;";
		if($reader->getAttribute("fo:color"))
			$content .= "color:".$reader->getAttribute("fo:color").";";
		if($reader->getAttribute("fo:text-align")) {
			$array = array("start" => "left","center" => "center","end" => "right","justify" => "justify");
			$content .= "text-align:".$array[$reader->getAttribute("fo:text-align")].";";
		}
		$this->content = $content;
	}
	
	public function meta () {
		$content = file_get_contents($this->path.'/'.$this->filename.'/meta.xml');
		$metaReader = new XMLReader;
		$metaReader->XML($content);
		while($metaReader->read()) {
			if($metaReader->nodeType == XMLReader::ELEMENT)
				switch($metaReader->name) {
					case 'dc:title':
						$id = "title";
						break;
					case 'dc:creator':
						$id = "creator";
						break;
					case 'dc:date':
						$id = "dc:date";
						break;
				}
			if($metaReader->nodeType == XMLReader::TEXT)
				$meta[$id] = $metaReader->readString();
		}
		$metaReader->close();
		return $meta;
	}
	
	public function odt2html () {
		$tmp = "";
		$tmp_end = "";
		$this->unzip();
		$content = file_get_contents($this->path.'/'.$this->filename.'/content.xml');
		$reader = $this->reader;
		$echo_content = true;
		$cssWrited = 0;
		$content = str_replace("> <",">&amp;nbsp;<",$content);
		$reader->XML($content);
		while ($reader->read()) {
			if(!$cssWrited) {
				echo '<style id="styles1" language="text/css">';
				$cssWrited = 1;
			}
			if($reader->nodeType == XMLReader::ELEMENT && $reader->name == "office:automatic-styles") {
				$cssData = $reader->readInnerXML();
			}
			if($reader->nodeType == XMLReader::ELEMENT && $reader->name == "style:style") {
				$name = $reader->getAttribute("style:name");
				$tmp = " .".$name." {";
			}
			if($reader->getAttribute("style:parent-style-name")) {
				$names[$name] = $reader->getAttribute("style:parent-style-name");
			}
			if($reader->nodeType == XMLReader::ELEMENT && $reader->name == "text:list-style") {
				$name = $reader->getAttribute("style:name");
				$this->c = 1;
				$this->z = 1;
				$tmp = " .".$name." {";
			}
			$this->attributes($reader);
			if($reader->nodeType == XMLReader::END_ELEMENT && $reader->name == "text:list-style") {
				$tmp_end = "}";
			}
			if($reader->nodeType == XMLReader::END_ELEMENT && $reader->name == "style:style") {
				$tmp_end = "}";
			}			
			if($this->content != " ") 
				echo $tmp.$this->content.$tmp_end;
			if ($reader->nodeType == XMLReader::ELEMENT) {
				if($cssWrited == 1 && $reader->name == 'office:body') {
					echo '</style>';
					echo '<body><section id="page">';
					$cssWrited = 2;
				}
				switch($reader->name) {
					case "text:p":
						if(!$reader->isEmptyElement) {
							echo "<p";
							echo " class='".$reader->getAttribute("text:style-name");
							echo "' >";
							break;
						} else {
							echo '<br/>';
							break;
						}

					case "table:table":
						echo "<table>";
						break;

					case "text:index-title-template":
						$echo_content = false;
						break;

					case "text:a":
						echo "<a href='".$reader->getAttribute("xlink:href")."' target='".$reader->getAttribute("office:target-frame-name")."'>";
						break;

					case "draw:frame":
						echo "<img style='width:".$reader->getAttribute("svg:width")."; height:".$reader->getAttribute("svg:height").";' src='tmp/".$_GET['file'].'/';
						$reader->read();
						echo $reader->getAttribute("xlink:href")."' />";
						break;

					case "text:h":
						$noSpan = true;
						switch($reader->getAttribute("text:style-name")) {
							case "Heading_20_1":
								echo "<h1>";
								$textH = "h1";
								break;
							case "Heading_20_2":
								echo "<h2>";
								$textH = "h2";
								break;
							case "Heading_20_3":
								echo "<h3>";
								$textH = "h3";
								break;
							case "Heading_20_4":
								echo "<h4>";
								$textH = "h4";
								break;
							case "Heading_20_5":
								echo "<h5>";
								$textH = "h5";
								break;
							case "Heading_20_6":
								echo "<h6>";
								$textH = "h6";
								break;
							default:
								switch($names[$reader->getAttribute("text:style-name")]) {
									case "Heading_20_1":
										$textH = "h1";
										echo "<h1>";
										break;
									case "Heading_20_2":
										echo "<h2>";
										$textH = "h2";
										break;
									case "Heading_20_3":
										echo "<h3>";
										$textH = "h3";
										break;
									case "Heading_20_4":
										echo "<h4>";
										$textH = "h4";
										break;
									case "Heading_20_5":
										echo "<h5>";
										$textH = "h5";
										break;
									case "Heading_20_6":
										echo "<h6>";
										$textH = "h6";
										break;
								}
						}
						break;

					case "table:table-row":
						echo "<tr>";
						break;

					case "table:table-cell":
						echo "<td>";
						break;

					case "text:line-break":
						echo '<br/>';
						break;

					case "text:list":
						echo "<ul";
						echo " class='".$reader->getAttribute("text:style-name")."' ";
						if($reader->getAttribute('xml:id'))
							echo " xml:id='".$reader->getAttribute('xml:id')."' ";
						echo ">";
						break;

					case "text:list-item":
						echo "<li";
						echo " class='".$reader->getAttribute("text:style-name")."' ";
						echo ">";
						break;

					case "text:tab":
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						break;

					case "text:span":
						if(! $noSpan) {
							echo '<span';
							echo " class='".$reader->getAttribute("text:style-name")."' ";
							echo '>';
							if(!$reader->readInnerXML()) {
								echo " ";
							}
						}
						break;

					case "text:bookmark-start":
						echo "<a name='".$reader->getAttribute("text:name")."' ></A>";
						break;

					case "draw:enhanced-geometry":
						echo "<svg ";
						$ex = explode(" ",$reader->getAttribute("svg:viewBox"));
						echo "width='".$ex[2]."' height='".$ex[3]."px'>";
						break;
					}
			} elseif ($reader->nodeType == XMLReader::END_ELEMENT) {
				switch($reader->name) {
					case "text:p":
						if(!$reader->isEmptyElement) {
							echo "</p>";
							break;
						} else {
							break;
						}

					case "text:h":
						echo '</'.$textH.'>';
						break;
					case "text:list":
						echo "</ul>";
						break;

					case "table:table-cell":
						echo "</td>";
						break;

					case "table:table":
						echo "</table>";
						break;

					case "table:table-row":
						echo "</tr>";
						break;

					case "text:list-item":
						echo "</li>";
						break;

					case "text:a":
						echo "</a>";
						break;

					case "text:span":
						if(! $noSpan) {
							echo '</span>';
						} else {
							$noSpan = false;
						}
						break;
				}
			} elseif ($reader->nodeType == XMLReader::TEXT) {
				if($echo_content) {
					echo $reader->readString();
				} else {
					$echo_content = true;
				}
			}
		}
		return $cssData;
	}
	
	public function html2odt($content) {
		$echo_content = true;
		$contentXml = '<?xml version="1.0" encoding="UTF-8"?><office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:rpt="http://openoffice.org/2005/report" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:officeooo="http://openoffice.org/2009/office" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:css3t="http://www.w3.org/TR/css3-text/" office:version="1.2">';
		$reader = new XMLReader();
		$reader->xml($content);
		$cssWrited = 0;
		$contentXml .= '<office:automatic-styles></office:automatic-styles><office:body><office:text>';
		while ($reader->read()) {
			if ($reader->nodeType == XMLReader::ELEMENT) {
				switch($reader->name) {
					case "p":
						if(!$reader->isEmptyElement) {
							$contentXml .= "<text:p";
							if($reader->getAttribute("class"))
								$contentXml .= " text:style-name='".$reader->getAttribute("class")."' ";
							$contentXml .= ">";
							break;
						} else {
							$contentXml .= '<text:p/>';
							break;
						}
						
					case "table":
						$contentXml .= "<table:table>";
						break;

					case "a":
						if($reader->getAttribute("name")) {
							$contentXml .= '<text:bookmark-start text:name="'.$reader->getAttribute("name").'"/>';
							$bookmark = true;
						} else {
							$contentXml .= "<text:a xlink:href='".$reader->getAttribute("href")."' office:target-frame-name='".$reader->getAttribute("target")."'>";
						}
						break;
						
					case "h1":
						$contentXml .= '<text:h text:style-name="Heading_20_1">';
						break;
						
					case "h2":
						$contentXml .= '<text:h text:style-name="Heading_20_2">';
						break;
						
					case "h3":
						$contentXml .= '<text:h text:style-name="Heading_20_3">';
						break;

					case "tr":
						$contentXml .= "<table:table-row>";
						break;

					case "td":
						$contentXml .= "<table:table-cell>";
						break;

					case "br":
						$contentXml .= '<text:line-break>';
						break;

					case "ul":
						$contentXml .= "<text:list";
						if($reader->getAttribute("class")) 
							$contentXml .= " text:style-name='".$reader->getAttribute("class")."' ";
						if($reader->getAttribute('xml:id'))
							$contentXml .= " xml:id='".$reader->getAttribute('xml:id')."' ";
						$contentXml .= ">";
						break;

					case "li":
						$contentXml .= "<text:list-item";
						if($reader->getAttribute("class"))
							$contentXml .= " text:style-name='".$reader->getAttribute("class")."' ";
						$contentXml .= ">";
						break;

					case "span":
						$contentXml .= '<text:span';
						$contentXml .= " text:style-name='".$reader->getAttribute("class")."' ";
						$contentXml .= " text:name='".$reader->getAttribute("id")."' ";
						$contentXml .= '>';
						if(!$reader->readInnerXML()) {
							$contentXml .= " ";
						}
						break;
						
					case "style":
						$echo_content = false;
						break;

					case "draw:enhanced-geometry":
						echo "<svg ";
						$ex = explode(" ",$reader->getAttribute("svg:viewBox"));
						echo "width='".$ex[2]."' height='".$ex[3]."px'>";
						break;
					}
			} elseif ($reader->nodeType == XMLReader::END_ELEMENT) {
				switch($reader->name) {
					case "p":
						if(!$reader->isEmptyElement) {
							$contentXml .= "</text:p>";
							break;
						} else {
							break;
						}
						
					case "h1":
						$contentXml .= '</text:h>';
						break;
						
					case "h2":
						$contentXml .= '</text:h>';
						break;
						
					case "h3":
						$contentXml .= '</text:h>';
						break;

					case "ul":
						$contentXml .= "</text:list>";
						break;

					case "td":
						$contentXml .= "</table:table-cell>";
						break;

					case "table":
						$contentXml .= "</table:table>";
						break;

					case "tr":
						$contentXml .= "</table:table-row>";
						break;

					case "li":
						$contentXml .= "</text:list-item>";
						break;

					case "a":
						if(!$bookmark) {
							$contentXml .= "</text:a>";
						}
						break;

					case "span":
						$contentXml .= '</text:span>';
						break;
				}
			} elseif ($reader->nodeType == XMLReader::TEXT) {
				if($echo_content) {
					if($reader->readString() == "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;") {
						$contentXml .= "<text:tab/>";
					} else {
						$contentXml .= $reader->readString();
					}
					if($bookmark) {
						$contentXml .= "<text:bookmark-end/>";
						$bookmark = false;
					}
				} else {
					$echo_content = true;
				}
			}
		}
		$contentXml .= '</office:text></office:body></office:document-content>';
		file_put_contents($this->path.'/'.$this->filename.'/content.xml',$contentXml);
		$this->zip();
	}
}
?>
