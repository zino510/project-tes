<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

namespace Google\Service\SA360;

class GoogleAdsSearchads360V0ResourcesUserLocationView extends \Google\Model
{
  /**
   * @var string
   */
  public $countryCriterionId;
  /**
   * @var string
   */
  public $resourceName;
  /**
   * @var bool
   */
  public $targetingLocation;

  /**
   * @param string
   */
  public function setCountryCriterionId($countryCriterionId)
  {
    $this->countryCriterionId = $countryCriterionId;
  }
  /**
   * @return string
   */
  public function getCountryCriterionId()
  {
    return $this->countryCriterionId;
  }
  /**
   * @param string
   */
  public function setResourceName($resourceName)
  {
    $this->resourceName = $resourceName;
  }
  /**
   * @return string
   */
  public function getResourceName()
  {
    return $this->resourceName;
  }
  /**
   * @param bool
   */
  public function setTargetingLocation($targetingLocation)
  {
    $this->targetingLocation = $targetingLocation;
  }
  /**
   * @return bool
   */
  public function getTargetingLocation()
  {
    return $this->targetingLocation;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(GoogleAdsSearchads360V0ResourcesUserLocationView::class, 'Google_Service_SA360_GoogleAdsSearchads360V0ResourcesUserLocationView');
