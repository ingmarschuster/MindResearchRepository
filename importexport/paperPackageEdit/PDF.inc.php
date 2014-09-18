<?php

include('plugins/importexport/paperPackageUpload/fpdf17/fpdf.php');

class PDF extends FPDF {

// Page header
    function Header()
	{
            // Arial bold 15
            $this->SetFont('Arial','B',15);
	    
	    $journal =& Request::getJournal();
	    $title = $journal->getLocalizedPageHeaderTitle(true);
	    
	    // Calculate width of title and position
    	    $w = $this->GetStringWidth($title)+6;
            $this->SetX((210-$w)/2);
	    
	    //Set color black
	    $this->SetTextColor(3,0,0);
	    
	    // Move to the right
	    $this->Cell($w,10,$title,0,0,'C');
	    
	    // Line break
	    $this->Ln(20);
	}

       
       function createHandleLink($handle){

	$handleLink = "http://hdl.handle.net/" . $handle;

	return $handleLink;
			
       }
      


}


?>
